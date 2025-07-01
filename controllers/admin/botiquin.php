<?php
// Controlador para gestión administrativa del botiquín
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/AdminAuthService.php';

// Verificar autenticación admin
$adminAuth = new AdminAuthService();
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin/login');
    exit;
}

$admin = $adminAuth->getAdminActual();
$permissions = $adminAuth->getPermissionsService();

// Limpiar output y configurar headers
if (ob_get_level()) {
    ob_clean();
}
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'dashboard':
                    getDashboard($permissions);
                    break;
                case 'inventario':
                    getInventario($permissions);
                    break;
                case 'solicitudes':
                    getSolicitudes($permissions);
                    break;
                case 'instalaciones':
                    getInstalaciones($permissions);
                    break;
                case 'elemento':
                    getElemento($permissions);
                    break;
                default:
                    getDashboard($permissions);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'crear_elemento':
                    crearElemento($permissions, $admin);
                    break;
                case 'actualizar_elemento':
                    actualizarElemento($permissions, $admin);
                    break;
                case 'eliminar_elemento':
                    eliminarElemento($permissions, $admin);
                    break;
                case 'actualizar_solicitud':
                    actualizarSolicitud($permissions, $admin);
                    break;
                case 'importar_csv':
                    importarCSV($permissions, $admin);
                    break;
                default:
                    throw new Exception('Acción no válida');
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// Función para obtener dashboard
function getDashboard($permissions) {
    $instalaciones = $permissions->getInstalacionesPermitidas();
    $inventario = $permissions->getInventarioBotiquinPermitido();
    $solicitudes = $permissions->getSolicitudesMaterialPermitidas();
    
    // Estadísticas generales
    $stats = [
        'total_instalaciones' => count($instalaciones),
        'total_elementos' => count($inventario),
        'solicitudes_pendientes' => count(array_filter($solicitudes, function($s) { return $s['estado'] === 'pendiente'; })),
        'elementos_bajo_minimos' => count(array_filter($inventario, function($i) { return $i['cantidad_actual'] <= 5; }))
    ];
    
    // Resumen por instalación
    $resumen_instalaciones = [];
    foreach ($instalaciones as $instalacion) {
        $inventario_instalacion = array_filter($inventario, function($i) use ($instalacion) {
            return $i['instalacion_id'] == $instalacion['id'];
        });
        
        $solicitudes_instalacion = array_filter($solicitudes, function($s) use ($instalacion) {
            return $s['instalacion_id'] == $instalacion['id'] && $s['estado'] === 'pendiente';
        });
        
        $resumen_instalaciones[] = [
            'id' => $instalacion['id'],
            'nombre' => $instalacion['nombre'],
            'coordinador_nombre' => $instalacion['coordinador_nombre'],
            'total_elementos' => count($inventario_instalacion),
            'elementos_bajo_minimos' => count(array_filter($inventario_instalacion, function($i) { return $i['cantidad_actual'] <= 5; })),
            'solicitudes_pendientes' => count($solicitudes_instalacion)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'instalaciones' => $resumen_instalaciones,
        'permisos' => $permissions->getResumenPermisos()
    ]);
}

// Función para obtener inventario
function getInventario($permissions) {
    $instalacionId = $_GET['instalacion_id'] ?? null;
    $categoria = $_GET['categoria'] ?? null;
    $busqueda = $_GET['busqueda'] ?? '';
    
    $inventario = $permissions->getInventarioBotiquinPermitido($instalacionId);
    
    // Filtrar por categoría si se especifica
    if ($categoria && $categoria !== 'todos') {
        $inventario = array_filter($inventario, function($item) use ($categoria) {
            return $item['categoria'] === $categoria;
        });
    }
    
    // Filtrar por búsqueda
    if (!empty($busqueda)) {
        $inventario = array_filter($inventario, function($item) use ($busqueda) {
            return stripos($item['nombre_elemento'], $busqueda) !== false ||
                   stripos($item['observaciones'], $busqueda) !== false;
        });
    }
    
    // Agrupar por instalación
    $inventario_agrupado = [];
    foreach ($inventario as $item) {
        $instalacion_nombre = $item['instalacion_nombre'];
        if (!isset($inventario_agrupado[$instalacion_nombre])) {
            $inventario_agrupado[$instalacion_nombre] = [];
        }
        $inventario_agrupado[$instalacion_nombre][] = $item;
    }
    
    echo json_encode([
        'success' => true,
        'inventario' => $inventario_agrupado,
        'total_elementos' => count($inventario)
    ]);
}

// Función para obtener solicitudes
function getSolicitudes($permissions) {
    $instalacionId = $_GET['instalacion_id'] ?? null;
    $estado = $_GET['estado'] ?? null;
    
    $solicitudes = $permissions->getSolicitudesMaterialPermitidas($instalacionId);
    
    // Filtrar por estado si se especifica
    if ($estado && $estado !== 'todos') {
        $solicitudes = array_filter($solicitudes, function($solicitud) use ($estado) {
            return $solicitud['estado'] === $estado;
        });
    }
    
    // Decodificar JSON de elementos solicitados
    foreach ($solicitudes as &$solicitud) {
        $solicitud['elementos_solicitados'] = json_decode($solicitud['elementos_solicitados'], true);
    }
    
    echo json_encode([
        'success' => true,
        'solicitudes' => array_values($solicitudes)
    ]);
}

// Función para obtener lista de instalaciones
function getInstalaciones($permissions) {
    $instalaciones = $permissions->getInstalacionesPermitidas();
    
    echo json_encode([
        'success' => true,
        'instalaciones' => $instalaciones
    ]);
}

// Función para obtener un elemento específico
function getElemento($permissions) {
    $elementoId = $_GET['elemento_id'] ?? null;
    if (!$elementoId) {
        throw new Exception('ID de elemento requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener elemento con verificación de permisos
    $stmt = $db->prepare("
        SELECT ib.*, i.nombre as instalacion_nombre, c.nombre as coordinador_nombre,
               s.nombre as ultima_actualizacion_por
        FROM inventario_botiquin ib
        INNER JOIN instalaciones i ON ib.instalacion_id = i.id
        INNER JOIN coordinadores c ON i.coordinador_id = c.id
        LEFT JOIN socorristas s ON ib.socorrista_ultima_actualizacion = s.id
        WHERE ib.id = ? AND ib.activo = 1
    ");
    
    $stmt->execute([$elementoId]);
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$elemento) {
        throw new Exception('Elemento no encontrado');
    }
    
    // Verificar permisos
    if (!$permissions->puedeAccederInstalacion($elemento['instalacion_id'])) {
        throw new Exception('Sin permisos para acceder a este elemento');
    }
    
    echo json_encode([
        'success' => true,
        'elemento' => $elemento
    ]);
}

// Función para crear elemento
function crearElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $required = ['instalacion_id', 'nombre_elemento', 'categoria', 'cantidad_actual', 'unidad_medida'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Verificar permisos de instalación
    if (!$permissions->puedeAccederInstalacion($input['instalacion_id'])) {
        throw new Exception('Sin permisos para gestionar esta instalación');
    }
    
    // Validar cantidad
    if (!is_numeric($input['cantidad_actual']) || $input['cantidad_actual'] < 0) {
        throw new Exception('La cantidad debe ser un número positivo');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar elemento duplicado
    $stmt = $db->prepare("
        SELECT id FROM inventario_botiquin 
        WHERE instalacion_id = ? AND nombre_elemento = ? AND activo = 1
    ");
    $stmt->execute([$input['instalacion_id'], trim($input['nombre_elemento'])]);
    
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un elemento con ese nombre en la instalación');
    }
    
    // Crear elemento
    $stmt = $db->prepare("
        INSERT INTO inventario_botiquin 
        (instalacion_id, nombre_elemento, categoria, cantidad_actual, unidad_medida, observaciones, socorrista_ultima_actualizacion)
        VALUES (?, ?, ?, ?, ?, ?, NULL)
    ");
    
    $stmt->execute([
        $input['instalacion_id'],
        trim($input['nombre_elemento']),
        $input['categoria'],
        $input['cantidad_actual'],
        trim($input['unidad_medida']),
        trim($input['observaciones'] ?? '')
    ]);
    
    $elementoId = $db->lastInsertId();
    
    // Crear registro en historial (simular socorrista admin)
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
        VALUES (?, 1, 'creado', 0, ?, ?)
    ");
    
    $stmt->execute([
        $elementoId,
        $input['cantidad_actual'],
        'Creado desde panel administrativo por ' . $admin['nombre']
    ]);
    
    echo json_encode([
        'success' => true,
        'elemento_id' => $elementoId,
        'message' => 'Elemento creado exitosamente'
    ]);
}

// Función para actualizar elemento
function actualizarElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('ID de elemento requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener elemento actual
    $stmt = $db->prepare("
        SELECT * FROM inventario_botiquin 
        WHERE id = ? AND activo = 1
    ");
    $stmt->execute([$input['id']]);
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$elemento) {
        throw new Exception('Elemento no encontrado');
    }
    
    // Verificar permisos
    if (!$permissions->puedeAccederInstalacion($elemento['instalacion_id'])) {
        throw new Exception('Sin permisos para gestionar este elemento');
    }
    
    // Validar cantidad si se proporciona
    if (isset($input['cantidad_actual'])) {
        if (!is_numeric($input['cantidad_actual']) || $input['cantidad_actual'] < 0) {
            throw new Exception('La cantidad debe ser un número positivo');
        }
    }
    
    // Preparar campos a actualizar
    $updates = [];
    $params = [];
    
    if (isset($input['nombre_elemento'])) {
        $updates[] = 'nombre_elemento = ?';
        $params[] = trim($input['nombre_elemento']);
    }
    
    if (isset($input['categoria'])) {
        $updates[] = 'categoria = ?';
        $params[] = $input['categoria'];
    }
    
    if (isset($input['cantidad_actual'])) {
        $updates[] = 'cantidad_actual = ?';
        $params[] = $input['cantidad_actual'];
    }
    
    if (isset($input['unidad_medida'])) {
        $updates[] = 'unidad_medida = ?';
        $params[] = trim($input['unidad_medida']);
    }
    
    if (isset($input['observaciones'])) {
        $updates[] = 'observaciones = ?';
        $params[] = trim($input['observaciones']);
    }
    
    if (empty($updates)) {
        throw new Exception('No hay campos para actualizar');
    }
    
    $params[] = $input['id'];
    
    // Actualizar elemento
    $stmt = $db->prepare("
        UPDATE inventario_botiquin 
        SET " . implode(', ', $updates) . ", fecha_ultima_actualizacion = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute($params);
    
    // Crear registro en historial si cambió la cantidad
    if (isset($input['cantidad_actual']) && $input['cantidad_actual'] != $elemento['cantidad_actual']) {
        $stmt = $db->prepare("
            INSERT INTO historial_botiquin 
            (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
            VALUES (?, 1, 'actualizado', ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['id'],
            $elemento['cantidad_actual'],
            $input['cantidad_actual'],
            'Actualizado desde panel administrativo por ' . $admin['nombre']
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento actualizado exitosamente'
    ]);
}

// Función para eliminar elemento
function eliminarElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('ID de elemento requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener elemento
    $stmt = $db->prepare("
        SELECT * FROM inventario_botiquin 
        WHERE id = ? AND activo = 1
    ");
    $stmt->execute([$input['id']]);
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$elemento) {
        throw new Exception('Elemento no encontrado');
    }
    
    // Verificar permisos
    if (!$permissions->puedeAccederInstalacion($elemento['instalacion_id'])) {
        throw new Exception('Sin permisos para gestionar este elemento');
    }
    
    // Marcar como inactivo (soft delete)
    $stmt = $db->prepare("
        UPDATE inventario_botiquin 
        SET activo = 0, fecha_ultima_actualizacion = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$input['id']]);
    
    // Crear registro en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
        VALUES (?, 1, 'eliminado', ?, 0, ?)
    ");
    
    $stmt->execute([
        $input['id'],
        $elemento['cantidad_actual'],
        'Eliminado desde panel administrativo por ' . $admin['nombre']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento eliminado exitosamente'
    ]);
}

// Función para actualizar estado de solicitud
function actualizarSolicitud($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['estado'])) {
        throw new Exception('ID de solicitud y estado requeridos');
    }
    
    $estados_validos = ['pendiente', 'enviada', 'recibida'];
    if (!in_array($input['estado'], $estados_validos)) {
        throw new Exception('Estado no válido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener solicitud
    $stmt = $db->prepare("
        SELECT * FROM solicitudes_material 
        WHERE id = ?
    ");
    $stmt->execute([$input['id']]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$solicitud) {
        throw new Exception('Solicitud no encontrada');
    }
    
    // Verificar permisos
    if (!$permissions->puedeAccederInstalacion($solicitud['instalacion_id'])) {
        throw new Exception('Sin permisos para gestionar esta solicitud');
    }
    
    // Actualizar estado
    $stmt = $db->prepare("
        UPDATE solicitudes_material 
        SET estado = ?, fecha_envio = CASE WHEN ? = 'enviada' THEN NOW() ELSE fecha_envio END
        WHERE id = ?
    ");
    
    $stmt->execute([$input['estado'], $input['estado'], $input['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado de solicitud actualizado exitosamente'
    ]);
}

// Función para importar CSV
function importarCSV($permissions, $admin) {
    if (!isset($_FILES['csv_file'])) {
        throw new Exception('Archivo CSV requerido');
    }
    
    $instalacionId = $_POST['instalacion_id'] ?? null;
    if (!$instalacionId) {
        throw new Exception('ID de instalación requerido');
    }
    
    // Verificar permisos
    if (!$permissions->puedeAccederInstalacion($instalacionId)) {
        throw new Exception('Sin permisos para gestionar esta instalación');
    }
    
    $file = $_FILES['csv_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir archivo CSV');
    }
    
    // Leer CSV
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        throw new Exception('No se pudo leer el archivo CSV');
    }
    
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    
    try {
        $lineNumber = 0;
        $importados = 0;
        $errores = [];
        
        // Saltar header si existe
        if (($header = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;
        }
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $lineNumber++;
            
            if (count($data) < 4) {
                $errores[] = "Línea $lineNumber: Datos insuficientes";
                continue;
            }
            
            $nombre = trim($data[0] ?? '');
            $categoria = trim($data[1] ?? 'otros');
            $cantidad = trim($data[2] ?? '0');
            $unidad = trim($data[3] ?? 'unidades');
            $observaciones = trim($data[4] ?? '');
            
            if (empty($nombre)) {
                $errores[] = "Línea $lineNumber: Nombre de elemento vacío";
                continue;
            }
            
            if (!is_numeric($cantidad) || $cantidad < 0) {
                $errores[] = "Línea $lineNumber: Cantidad inválida para $nombre";
                continue;
            }
            
            // Verificar si existe
            $stmt = $db->prepare("
                SELECT id FROM inventario_botiquin 
                WHERE instalacion_id = ? AND nombre_elemento = ? AND activo = 1
            ");
            $stmt->execute([$instalacionId, $nombre]);
            
            if ($stmt->fetch()) {
                // Actualizar existente
                $stmt = $db->prepare("
                    UPDATE inventario_botiquin 
                    SET cantidad_actual = ?, unidad_medida = ?, observaciones = ?, fecha_ultima_actualizacion = NOW()
                    WHERE instalacion_id = ? AND nombre_elemento = ? AND activo = 1
                ");
                $stmt->execute([$cantidad, $unidad, $observaciones, $instalacionId, $nombre]);
            } else {
                // Crear nuevo
                $stmt = $db->prepare("
                    INSERT INTO inventario_botiquin 
                    (instalacion_id, nombre_elemento, categoria, cantidad_actual, unidad_medida, observaciones)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$instalacionId, $nombre, $categoria, $cantidad, $unidad, $observaciones]);
            }
            
            $importados++;
        }
        
        $db->commit();
        fclose($handle);
        
        echo json_encode([
            'success' => true,
            'importados' => $importados,
            'errores' => $errores,
            'message' => "Importación completada: $importados elementos procesados"
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        fclose($handle);
        throw $e;
    }
}
?>