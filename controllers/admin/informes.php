<?php
// NOTA: No necesitamos session_start() aquí porque index.php ya maneja la sesión

// Incluir servicio de autenticación admin
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();

// Sistema de exportación funcionando correctamente

// Verificar si está logueado como admin (usando el método correcto)
if (!$adminAuth->estaAutenticadoAdmin()) {
    error_log("DEBUG informes.php: Usuario no autorizado");
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/app.php';

// Obtener conexión a la base de datos_json
$db = Database::getInstance()->getConnection();

// Manejar peticiones
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'export_control_flujo':
            exportControlFlujo($db);
            break;
        case 'export_incidencias':
            exportIncidencias($db);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}

function exportControlFlujo($db) {
    // Asegurar codificación UTF-8 en la conexión para esta consulta específica
    $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $filters = [
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null,
        'instalacion_id' => $_GET['instalacion_id'] ?? null,
        'socorrista_id' => $_GET['socorrista_id'] ?? null
    ];
    
    // Obtener datos_json
    $sql = "SELECT f.*, i.nombre as instalacion_nombre, i.aforo_maximo, s.nombre as socorrista_nombre 
            FROM formularios f 
            LEFT JOIN socorristas s ON f.socorrista_id = s.id 
            LEFT JOIN instalaciones i ON s.instalacion_id = i.id 
            WHERE f.tipo_formulario = 'control_flujo'";
    
    $params = [];
    
    if ($filters['fecha_inicio']) {
        $sql .= " AND DATE(f.fecha_creacion) >= ?";
        $params[] = $filters['fecha_inicio'];
    }
    
    if ($filters['fecha_fin']) {
        $sql .= " AND DATE(f.fecha_creacion) <= ?";
        $params[] = $filters['fecha_fin'];
    }
    
    if ($filters['instalacion_id']) {
        $sql .= " AND s.instalacion_id = ?";
        $params[] = $filters['instalacion_id'];
    }
    
    if ($filters['socorrista_id']) {
        $sql .= " AND f.socorrista_id = ?";
        $params[] = $filters['socorrista_id'];
    }
    
    $sql .= " ORDER BY f.fecha_creacion DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Primero, identificar todos los espacios únicos para crear las columnas dinámicamente
    $espaciosUnicos = [];
    foreach ($registros as $registro) {
        $datos_json = json_decode($registro['datos_json'], true);
        if (!$datos_json) continue;
        
        if (isset($datos_json['espacios']) && is_array($datos_json['espacios'])) {
            foreach ($datos_json['espacios'] as $espacio) {
                $nombreEspacio = $espacio['nombre'] ?? 'N/A';
                if (!in_array($nombreEspacio, $espaciosUnicos)) {
                    $espaciosUnicos[] = $nombreEspacio;
                }
            }
        }
    }
    
    // Crear headers dinámicos
    $headers = [
        'Fecha',
        'Franja Horaria',
        'Instalación',
        'Socorrista'
    ];
    
    // Añadir columnas para cada espacio único
    foreach ($espaciosUnicos as $espacio) {
        $headers[] = $espacio . ' - Personas';
        $headers[] = $espacio . ' - Observaciones';
    }
    
    // Añadir columnas finales
    $headers[] = 'Total Personas';
    $headers[] = 'Aforo Máximo';
    $headers[] = 'Porcentaje Ocupación';
    $headers[] = 'Observaciones Generales';
    
    $csvData = [];
    $csvData[] = $headers;
    
    foreach ($registros as $registro) {
        $datos_json = json_decode($registro['datos_json'], true);
        if (!$datos_json) continue;
        
        $fecha = new DateTime($registro['fecha_creacion']);
        $aforoMaximo = $registro['aforo_maximo'] ?? 0;
        
        // Detectar si es formato nuevo (con espacios) o antiguo
        if (isset($datos_json['espacios']) && is_array($datos_json['espacios'])) {
            // FORMATO NUEVO: Control de flujo por espacios
            $franjaHoraria = $datos_json['franja_horaria'] ?? $fecha->format('H:i');
            $observacionesGenerales = $datos_json['observaciones_generales'] ?? '';
            $totalPersonas = $datos_json['total_personas'] ?? 0;
            
            // Crear fila base
            $fila = [
                $datos_json['fecha'] ?? $fecha->format('d/m/Y'),
                $franjaHoraria,
                $registro['instalacion_nombre'] ?? 'N/A',
                $registro['socorrista_nombre'] ?? 'N/A'
            ];
            
            // Crear mapa de espacios del registro actual
            $espaciosRegistro = [];
            foreach ($datos_json['espacios'] as $espacio) {
                $espaciosRegistro[$espacio['nombre']] = [
                    'personas' => $espacio['personas'] ?? 0,
                    'observaciones' => $espacio['observaciones'] ?? ''
                ];
            }
            
            // Llenar columnas de espacios
            foreach ($espaciosUnicos as $espacio) {
                if (isset($espaciosRegistro[$espacio])) {
                    $fila[] = $espaciosRegistro[$espacio]['personas'];
                    $fila[] = $espaciosRegistro[$espacio]['observaciones'];
                } else {
                    $fila[] = ''; // Sin personas
                    $fila[] = ''; // Sin observaciones
                }
            }
            
            // Calcular porcentaje
            $porcentaje = $aforoMaximo > 0 ? round(($totalPersonas / $aforoMaximo) * 100, 1) : 0;
            
            // Añadir columnas finales
            $fila[] = $totalPersonas;
            $fila[] = $aforoMaximo;
            $fila[] = $porcentaje . '%';
            $fila[] = $observacionesGenerales;
            
            $csvData[] = $fila;
            
        } else {
            // FORMATO ANTIGUO: Compatibilidad con registros anteriores
            $personas = $datos_json['numero_personas'] ?? $datos_json['personas'] ?? 0;
            $porcentaje = $aforoMaximo > 0 ? round(($personas / $aforoMaximo) * 100, 1) : 0;
            
            $fila = [
                $fecha->format('d/m/Y'),
                $fecha->format('H:i'),
                $registro['instalacion_nombre'] ?? 'N/A',
                $registro['socorrista_nombre'] ?? 'N/A'
            ];
            
            // Llenar espacios vacíos para formato antiguo
            foreach ($espaciosUnicos as $espacio) {
                $fila[] = ''; // Sin personas por espacio
                $fila[] = ''; // Sin observaciones por espacio
            }
            
            // Columnas finales
            $fila[] = $personas; // Total
            $fila[] = $aforoMaximo;
            $fila[] = $porcentaje . '%';
            $fila[] = $datos_json['observaciones'] ?? '';
            
            $csvData[] = $fila;
        }
    }
    
    generateCSV($csvData, 'control_flujo_' . date('Y-m-d'));
}

function exportIncidencias($db) {
    // Asegurar codificación UTF-8 en la conexión para esta consulta específica
    $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    $filters = [
        'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
        'fecha_fin' => $_GET['fecha_fin'] ?? null,
        'instalacion_id' => $_GET['instalacion_id'] ?? null,
        'socorrista_id' => $_GET['socorrista_id'] ?? null
    ];
    
    // Obtener datos_json
    $sql = "SELECT f.*, i.nombre as instalacion_nombre, i.aforo_maximo, s.nombre as socorrista_nombre 
            FROM formularios f 
            LEFT JOIN socorristas s ON f.socorrista_id = s.id 
            LEFT JOIN instalaciones i ON s.instalacion_id = i.id 
            WHERE f.tipo_formulario = 'incidencias'";
    
    $params = [];
    
    if ($filters['fecha_inicio']) {
        $sql .= " AND DATE(f.fecha_creacion) >= ?";
        $params[] = $filters['fecha_inicio'];
    }
    
    if ($filters['fecha_fin']) {
        $sql .= " AND DATE(f.fecha_creacion) <= ?";
        $params[] = $filters['fecha_fin'];
    }
    
    if ($filters['instalacion_id']) {
        $sql .= " AND s.instalacion_id = ?";
        $params[] = $filters['instalacion_id'];
    }
    
    if ($filters['socorrista_id']) {
        $sql .= " AND f.socorrista_id = ?";
        $params[] = $filters['socorrista_id'];
    }
    
    $sql .= " ORDER BY f.fecha_creacion DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar datos_json para CSV
    $csvData = [];
    $csvData[] = [
        'Fecha',
        'Hora',
        'Instalación',
        'Socorrista',
        'Tipo Incidencia',
        'Descripción',
        'Gravedad',
        'Personas Afectadas',
        'Observaciones'
    ];
    
    foreach ($registros as $registro) {
        $datos_json = json_decode($registro['datos_json'], true);
        if (!$datos_json) continue;
        
        $fecha = new DateTime($registro['fecha_creacion']);
        
        $csvData[] = [
            $fecha->format('d/m/Y'),
            $fecha->format('H:i'),
            $registro['instalacion_nombre'] ?? 'N/A',
            $registro['socorrista_nombre'] ?? 'N/A',
            $datos_json['tipo'] ?? 'N/A',
            $datos_json['descripcion'] ?? '',
            $datos_json['gravedad'] ?? 'N/A',
            $datos_json['personas_afectadas'] ?? 0,
            $datos_json['observaciones'] ?? ''
        ];
    }
    
    generateCSV($csvData, 'incidencias_' . date('Y-m-d'));
}



function generateCSV($data, $filename) {
    // Headers alternativos - CSV como texto plano UTF-8
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    // Abrir output stream
    $output = fopen('php://output', 'w');
    
    // BOM UTF-8 (volvemos a intentar pero con text/plain)
    fwrite($output, "\xEF\xBB\xBF");
    
    // Escribir datos CSV manteniendo UTF-8 original
    foreach ($data as $row) {
        // Mantener datos exactamente como vienen, solo limpiar espacios
        $cleanRow = array_map(function($field) {
            if (is_string($field)) {
                return trim($field);
            }
            return $field;
        }, $row);
        
        // Usar ';' como separador y '"' como encerramiento
        fputcsv($output, $cleanRow, ';', '"');
    }
    
    fclose($output);
    exit;
}
?> 