<?php
// Controlador para gestión administrativa del botiquín
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/AdminAuthService.php';

// Verificar autenticación admin
$adminAuth = new AdminAuthService();
if (!$adminAuth->estaAutenticadoAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$admin = $adminAuth->getAdminActual();
$permissions = $adminAuth->getPermissionsService();

// Limpiar output y configurar headers
ob_clean();
error_reporting(0);
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
        'solicitudes_pendientes' => count(array_filter($solicitudes, function($s) { 
            return $s['estado'] === 'pendiente'; 
        }))
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
            'solicitudes_pendientes' => count($solicitudes_instalacion)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'instalaciones' => $resumen_instalaciones
    ]);
}

// Función para obtener inventario
function getInventario($permissions) {
    $instalacionId = $_GET['instalacion_id'] ?? null;
    $busqueda = $_GET['busqueda'] ?? '';
    
    $inventario = $permissions->getInventarioBotiquinPermitido($instalacionId);
    
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

// Función para crear elemento
function crearElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $required = ['nombre_elemento', 'categoria', 'cantidad_actual', 'unidad_medida', 'instalacion_id'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Verificar permisos de instalación
    if (!$permissions->puedeAccederInstalacion($input['instalacion_id'])) {
        throw new Exception('No tienes permisos para gestionar esta instalación');
    }
    
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
    $stmt->execute([$input['instalacion_id'], trim($input['nombre_elemento'])]);
    
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
        $input['instalacion_id'],
        trim($input['nombre_elemento']),
        $input['categoria'],
        $input['cantidad_actual'],
        trim($input['unidad_medida']),
        trim($input['observaciones'] ?? ''),
        null // Acción administrativa - no asignar socorrista específico
    ]);
    
    $elementoId = $db->lastInsertId();
    
    // Registrar en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
        VALUES (?, ?, 'creado', 0, ?, ?)
    ");
    
    $stmt->execute([
        $elementoId,
        null, // Acción administrativa - no asignar socorrista específico
        $input['cantidad_actual'],
        'Elemento creado desde panel administrativo por ' . $admin['nombre'] . ' (' . $admin['email'] . ')'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento creado correctamente',
        'elemento_id' => $elementoId
    ]);
}

// Función para actualizar elemento
function actualizarElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['cantidad_actual'])) {
        throw new Exception('ID y cantidad son requeridos');
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
        throw new Exception('No tienes permisos para gestionar esta instalación');
    }
    
    // Validar cantidad
    if (!is_numeric($input['cantidad_actual']) || $input['cantidad_actual'] < 0) {
        throw new Exception('La cantidad debe ser un número positivo');
    }
    
    $cantidadAnterior = $elemento['cantidad_actual'];
    
    // Actualizar elemento
    $stmt = $db->prepare("
        UPDATE inventario_botiquin 
        SET cantidad_actual = ?, observaciones = ?, fecha_ultima_actualizacion = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $input['cantidad_actual'],
        trim($input['observaciones'] ?? $elemento['observaciones']),
        $input['id']
    ]);
    
    // Registrar en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
        VALUES (?, ?, 'actualizado', ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['id'],
        null, // Acción administrativa - no asignar socorrista específico
        $cantidadAnterior,
        $input['cantidad_actual'],
        'Actualizado desde panel administrativo por ' . $admin['nombre'] . ' (' . $admin['email'] . ')'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento actualizado correctamente'
    ]);
}

// Función para eliminar elemento
function eliminarElemento($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('ID del elemento requerido');
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
        throw new Exception('No tienes permisos para gestionar esta instalación');
    }
    
    // Marcar como inactivo
    $stmt = $db->prepare("
        UPDATE inventario_botiquin 
        SET activo = 0, fecha_ultima_actualizacion = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$input['id']]);
    
    // Registrar en historial
    $stmt = $db->prepare("
        INSERT INTO historial_botiquin 
        (inventario_id, socorrista_id, accion, cantidad_anterior, cantidad_nueva, observaciones)
        VALUES (?, ?, 'eliminado', ?, 0, ?)
    ");
    
    $stmt->execute([
        $input['id'],
        null, // Acción administrativa - no asignar socorrista específico
        $elemento['cantidad_actual'],
        'Elemento eliminado desde panel administrativo por ' . $admin['nombre'] . ' (' . $admin['email'] . ')'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Elemento eliminado correctamente'
    ]);
}

// Función para actualizar solicitud
function actualizarSolicitud($permissions, $admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['estado'])) {
        throw new Exception('ID y estado son requeridos');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener solicitud actual
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
        throw new Exception('No tienes permisos para gestionar esta instalación');
    }
    
    // Actualizar solicitud
    $stmt = $db->prepare("
        UPDATE solicitudes_material 
        SET estado = ?, observaciones_coordinacion = ?, fecha_respuesta = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $input['estado'],
        trim($input['observaciones_coordinacion'] ?? ''),
        $input['id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Solicitud actualizada correctamente'
    ]);
}
?>