<?php
// Controlador para sistema de Botiquín
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/AuthService.php';

// Limpiar cualquier output previo y configurar headers JSON
ob_clean();
error_reporting(0); // Suprimir warnings para respuesta JSON limpia
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
$auth = new AuthService();
if (!$auth->estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$socorrista = $auth->getSocorristaActual();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'inventario':
                    obtenerInventario($socorrista);
                    break;
                case 'historial':
                    obtenerHistorial($socorrista);
                    break;
                case 'solicitudes':
                    obtenerSolicitudes($socorrista);
                    break;
                default:
                    obtenerInventario($socorrista);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'crear':
                    crearElemento($socorrista);
                    break;
                case 'actualizar':
                    actualizarElemento($socorrista);
                    break;
                case 'eliminar':
                    eliminarElemento($socorrista);
                    break;
                case 'solicitar':
                    crearSolicitud($socorrista);
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

// Función para obtener inventario de la instalación
function obtenerInventario($socorrista) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT ib.*, s.nombre as ultima_actualizacion_por
        FROM inventario_botiquin ib
        LEFT JOIN socorristas s ON ib.socorrista_ultima_actualizacion = s.id
        WHERE ib.instalacion_id = ? AND ib.activo = 1
        ORDER BY ib.categoria, ib.nombre_elemento
    ");
    
    $stmt->execute([$socorrista['instalacion_id']]);
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar por categoría
    $inventarioPorCategoria = [
        'medicamentos' => [],
        'material_curacion' => [],
        'instrumental' => [],
        'otros' => []
    ];
    
    foreach ($inventario as $item) {
        $inventarioPorCategoria[$item['categoria']][] = $item;
    }
    
    echo json_encode([
        'success' => true,
        'inventario' => $inventarioPorCategoria,
        'total_elementos' => count($inventario)
    ]);
}

// Función para obtener historial de cambios
function obtenerHistorial($socorrista) {
    $db = Database::getInstance()->getConnection();
    $elementoId = $_GET['elemento_id'] ?? null;
    
    if (!$elementoId) {
        throw new Exception('ID de elemento requerido');
    }
    
    $stmt = $db->prepare("
        SELECT hb.*, s.nombre as socorrista_nombre, ib.nombre_elemento
        FROM historial_botiquin hb
        JOIN socorristas s ON hb.socorrista_id = s.id
        JOIN inventario_botiquin ib ON hb.inventario_id = ib.id
        WHERE hb.inventario_id = ? AND ib.instalacion_id = ?
        ORDER BY hb.fecha_accion DESC
        LIMIT 20
    ");
    
    $stmt->execute([$elementoId, $socorrista['instalacion_id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'historial' => $historial
    ]);
}

// Función para obtener solicitudes
function obtenerSolicitudes($socorrista) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT sm.*, s.nombre as socorrista_nombre
        FROM solicitudes_material sm
        JOIN socorristas s ON sm.socorrista_id = s.id
        WHERE sm.instalacion_id = ?
        ORDER BY sm.fecha_solicitud DESC
        LIMIT 10
    ");
    
    $stmt->execute([$socorrista['instalacion_id']]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar JSON de elementos solicitados
    foreach ($solicitudes as &$solicitud) {
        $solicitud['elementos_solicitados'] = json_decode($solicitud['elementos_solicitados'], true);
    }
    
    echo json_encode([
        'success' => true,
        'solicitudes' => $solicitudes
    ]);
}

// Función para crear nuevo elemento
function crearElemento($socorrista) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $required = ['nombre_elemento', 'categoria', 'cantidad_actual', 'unidad_medida'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Sin validación de categorías - sistema simplificado
    
    // Validar cantidad
    if (!is_numeric($input['cantidad_actual']) || $input['cantidad_actual'] < 0) {
        throw new Exception('La cantidad debe ser un número positivo');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar que no existe elemento con mismo nombre en la instalación
    $stmt = $db->prepare("
        SELECT id FROM inventario_botiquin 
        WHERE instalacion_id = ? AND nombre_elemento = ? AND activo = 1
    ");
    $stmt->execute([$socorrista['instalacion_id'], trim($input['nombre_elemento'])]);
    
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un elemento con ese nombre en el inventario');
    }
    
    // Crear elemento
    $stmt = $db->prepare("
        INSERT INTO inventario_botiquin 
        (instalacion_id, nombre_elemento, categoria, cantidad_actual, unidad_medida, observaciones, socorrista_ultima_actualizacion)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $socorrista['instalacion_id'],
        trim($input['nombre_elemento']),
        $input['categoria'],
        (int)$input['cantidad_actual'],
        trim($input['unidad_medida']),
        trim($input['observaciones'] ?? ''),
        $socorrista['id']
    ]);
    
    $elementoId = $db->lastInsertId();
    
    // Registrar en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_nueva, observaciones)
        VALUES (?, ?, 'creado', ?, ?)
    ");
    
    $stmt->execute([
        $elementoId,
        $socorrista['id'],
        (int)$input['cantidad_actual'],
        'Elemento creado: ' . trim($input['nombre_elemento'])
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento creado correctamente',
        'elemento_id' => $elementoId
    ]);
}

// Función para actualizar elemento existente
function actualizarElemento($socorrista) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception('ID de elemento requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar que el elemento existe y pertenece a la instalación
    $stmt = $db->prepare("
        SELECT * FROM inventario_botiquin 
        WHERE id = ? AND instalacion_id = ? AND activo = 1
    ");
    $stmt->execute([$input['id'], $socorrista['instalacion_id']]);
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$elemento) {
        throw new Exception('Elemento no encontrado');
    }
    
    // Preparar campos a actualizar
    $campos = [];
    $valores = [];
    $cambios = [];
    
    if (isset($input['nombre_elemento']) && trim($input['nombre_elemento']) !== '') {
        $campos[] = 'nombre_elemento = ?';
        $valores[] = trim($input['nombre_elemento']);
        if ($elemento['nombre_elemento'] !== trim($input['nombre_elemento'])) {
            $cambios[] = "Nombre: '{$elemento['nombre_elemento']}' → '" . trim($input['nombre_elemento']) . "'";
        }
    }
    
    if (isset($input['categoria']) && trim($input['categoria']) !== '') {
        $campos[] = 'categoria = ?';
        $valores[] = trim($input['categoria']);
        if ($elemento['categoria'] !== trim($input['categoria'])) {
            $cambios[] = "Categoría: '{$elemento['categoria']}' → '" . trim($input['categoria']) . "'";
        }
    }
    
    if (isset($input['cantidad_actual']) && is_numeric($input['cantidad_actual']) && $input['cantidad_actual'] >= 0) {
        $campos[] = 'cantidad_actual = ?';
        $valores[] = (int)$input['cantidad_actual'];
        if ($elemento['cantidad_actual'] !== (int)$input['cantidad_actual']) {
            $cambios[] = "Cantidad: {$elemento['cantidad_actual']} → " . (int)$input['cantidad_actual'];
        }
    }
    
    if (isset($input['unidad_medida']) && trim($input['unidad_medida']) !== '') {
        $campos[] = 'unidad_medida = ?';
        $valores[] = trim($input['unidad_medida']);
        if ($elemento['unidad_medida'] !== trim($input['unidad_medida'])) {
            $cambios[] = "Unidad: '{$elemento['unidad_medida']}' → '" . trim($input['unidad_medida']) . "'";
        }
    }
    
    if (isset($input['observaciones'])) {
        $campos[] = 'observaciones = ?';
        $valores[] = trim($input['observaciones']);
    }
    
    if (empty($campos)) {
        throw new Exception('No hay campos para actualizar');
    }
    
    // Agregar campos de control
    $campos[] = 'socorrista_ultima_actualizacion = ?';
    $valores[] = $socorrista['id'];
    $valores[] = $input['id'];
    
    // Actualizar elemento
    $sql = "UPDATE inventario_botiquin SET " . implode(', ', $campos) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($valores);
    
    // Registrar en historial si hubo cambios
    if (!empty($cambios)) {
        $stmt = $db->prepare("
            INSERT INTO historial_botiquin 
            (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
            VALUES (?, ?, 'actualizado', ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['id'],
            $socorrista['id'],
            $elemento['cantidad_actual'],
            isset($input['cantidad_actual']) ? (int)$input['cantidad_actual'] : $elemento['cantidad_actual'],
            implode('; ', $cambios)
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento actualizado correctamente'
    ]);
}

// Función para eliminar elemento
function eliminarElemento($socorrista) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception('ID de elemento requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar que el elemento existe y pertenece a la instalación
    $stmt = $db->prepare("
        SELECT * FROM inventario_botiquin 
        WHERE id = ? AND instalacion_id = ? AND activo = 1
    ");
    $stmt->execute([$input['id'], $socorrista['instalacion_id']]);
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$elemento) {
        throw new Exception('Elemento no encontrado');
    }
    
    // Soft delete - marcar como inactivo
    $stmt = $db->prepare("
        UPDATE inventario_botiquin 
        SET activo = 0, socorrista_ultima_actualizacion = ?
        WHERE id = ?
    ");
    $stmt->execute([$socorrista['id'], $input['id']]);
    
    // Registrar en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, observaciones)
        VALUES (?, ?, 'eliminado', ?, ?)
    ");
    
    $stmt->execute([
        $input['id'],
        $socorrista['id'],
        $elemento['cantidad_actual'],
        'Elemento eliminado: ' . $elemento['nombre_elemento']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento eliminado correctamente'
    ]);
}

// Función para crear solicitud de material
function crearSolicitud($socorrista) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['elementos']) || !is_array($input['elementos']) || empty($input['elementos'])) {
        throw new Exception('Lista de elementos requerida');
    }
    
    // Validar elementos
    foreach ($input['elementos'] as $elemento) {
        if (!isset($elemento['nombre']) || trim($elemento['nombre']) === '') {
            throw new Exception('Nombre de elemento requerido');
        }
        if (!isset($elemento['cantidad']) || !is_numeric($elemento['cantidad']) || $elemento['cantidad'] <= 0) {
            throw new Exception('Cantidad válida requerida para: ' . $elemento['nombre']);
        }
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Crear solicitud
    $stmt = $db->prepare("
        INSERT INTO solicitudes_material 
        (instalacion_id, socorrista_id, elementos_solicitados, mensaje_adicional)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $socorrista['instalacion_id'],
        $socorrista['id'],
        json_encode($input['elementos'], JSON_UNESCAPED_UNICODE),
        trim($input['mensaje_adicional'] ?? '')
    ]);
    
    $solicitudId = $db->lastInsertId();
    
    // Responder inmediatamente al usuario
    echo json_encode([
        'success' => true,
        'message' => 'Solicitud enviada correctamente',
        'solicitud_id' => $solicitudId
    ]);
    
    // Intentar enviar email al coordinador en segundo plano
    try {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request(); // Terminar respuesta HTTP antes del email
        }
        
        enviarEmailSolicitud($socorrista, $input['elementos'], $input['mensaje_adicional'] ?? '', $solicitudId);
        
        // Marcar como enviada
        $stmt = $db->prepare("UPDATE solicitudes_material SET estado = 'enviada', fecha_envio = NOW() WHERE id = ?");
        $stmt->execute([$solicitudId]);
        
    } catch (Exception $e) {
        // Log error pero no fallar la solicitud
        error_log("Error enviando email solicitud: " . $e->getMessage());
    }
}

// Función para enviar email de solicitud
function enviarEmailSolicitud($socorrista, $elementos, $mensajeAdicional, $solicitudId) {
    // Cargar servicio de email
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../classes/EmailService.php';
        $emailService = new EmailService();
    } else {
        require_once __DIR__ . '/../classes/SimpleEmailService.php';
        $emailService = new SimpleEmailService();
    }
    
    // Preparar datos del formulario para el servicio de email
    $formularioData = [
        'id' => $solicitudId,
        'socorrista_id' => $socorrista['id'],
        'tipo_formulario' => 'solicitud_botiquin',
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'socorrista_nombre' => $socorrista['nombre'],
        'elementos_solicitados' => $elementos,
        'mensaje_adicional' => $mensajeAdicional
    ];
    
    // Usar el método estándar de envío que funciona en incidencias
    $resultado = $emailService->enviarNotificacionFormulario($formularioData);
    
    if (!$resultado) {
        throw new Exception('Error enviando email de solicitud');
    }
}
?>