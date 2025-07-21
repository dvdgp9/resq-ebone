<?php
// Controlador API para Gestión de Socorristas
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';

// Configurar respuesta JSON
header('Content-Type: application/json; charset=utf-8');
ob_clean();
error_reporting(0);

$adminAuth = new AdminAuthService();
$adminService = new AdminService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado como administrador']);
    exit;
}

// Inicializar conexión a la base de datos
$db = Database::getInstance()->getConnection();

$admin = $adminAuth->getAdminActual();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Listar socorristas según permisos
            $socorristas = $adminService->getSocorristas($admin);
            echo json_encode([
                'success' => true,
                'socorristas' => $socorristas
            ]);
            break;
            
        case 'POST':
            // Crear socorrista - Superadmins, admins y coordinadores
            if (!in_array($admin['tipo'], ['superadmin', 'admin', 'coordinador'])) {
                throw new Exception('Solo superadmins, admins y coordinadores pueden crear socorristas');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            // Si es coordinador, verificar que la instalación le pertenece
            if ($admin['tipo'] === 'coordinador' && !empty($input['instalacion_id'])) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM instalaciones WHERE id = ? AND coordinador_id = ?");
                $stmt->execute([$input['instalacion_id'], $admin['id']]);
                if (!$stmt->fetch()) {
                    throw new Exception('No tienes permisos para asignar socorristas a esta instalación');
                }
            }
            
            // Si es admin, usar AdminPermissionsService para verificar permisos
            if ($admin['tipo'] === 'admin' && !empty($input['instalacion_id'])) {
                require_once __DIR__ . '/../../classes/AdminPermissionsService.php';
                $permissions = new AdminPermissionsService($admin);
                if (!$permissions->puedeAccederInstalacion($input['instalacion_id'])) {
                    throw new Exception('No tienes permisos para asignar socorristas a esta instalación');
                }
            }
            
            $socorristaId = $adminService->crearSocorrista($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Socorrista creado correctamente',
                'socorrista_id' => $socorristaId
            ]);
            break;
            
        case 'PUT':
            // Actualizar socorrista - Superadmins, admins y coordinadores
            if (!in_array($admin['tipo'], ['superadmin', 'admin', 'coordinador'])) {
                throw new Exception('Solo superadmins, admins y coordinadores pueden actualizar socorristas');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de socorrista requerido');
            }
            
            // Si es coordinador, verificar que el socorrista pertenece a sus instalaciones
            if ($admin['tipo'] === 'coordinador') {
                $stmt = $db->prepare("
                    SELECT s.id FROM socorristas s 
                    JOIN instalaciones i ON s.instalacion_id = i.id 
                    WHERE s.id = ? AND i.coordinador_id = ?
                ");
                $stmt->execute([$input['id'], $admin['id']]);
                if (!$stmt->fetch()) {
                    throw new Exception('No tienes permisos para editar este socorrista');
                }
                
                // Verificar instalación de destino si se cambia
                if (!empty($input['instalacion_id'])) {
                    $stmt = $db->prepare("SELECT id FROM instalaciones WHERE id = ? AND coordinador_id = ?");
                    $stmt->execute([$input['instalacion_id'], $admin['id']]);
                    if (!$stmt->fetch()) {
                        throw new Exception('No tienes permisos para asignar socorristas a esta instalación');
                    }
                }
            }
            
            // Si es admin, usar AdminPermissionsService para verificar permisos
            if ($admin['tipo'] === 'admin') {
                require_once __DIR__ . '/../../classes/AdminPermissionsService.php';
                $permissions = new AdminPermissionsService($admin);
                
                // Verificar que puede acceder al socorrista actual
                if (!$permissions->puedeAccederSocorrista($input['id'])) {
                    throw new Exception('No tienes permisos para editar este socorrista');
                }
                
                // Verificar instalación de destino si se cambia
                if (!empty($input['instalacion_id']) && !$permissions->puedeAccederInstalacion($input['instalacion_id'])) {
                    throw new Exception('No tienes permisos para asignar socorristas a esta instalación');
                }
            }
            
            $adminService->actualizarSocorrista($input['id'], $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Socorrista actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar socorrista - Superadmins, admins y coordinadores
            if (!in_array($admin['tipo'], ['superadmin', 'admin', 'coordinador'])) {
                throw new Exception('Solo superadmins, admins y coordinadores pueden desactivar socorristas');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de socorrista requerido');
            }
            
            // Si es coordinador, verificar que el socorrista pertenece a sus instalaciones
            if ($admin['tipo'] === 'coordinador') {
                $stmt = $db->prepare("
                    SELECT s.id FROM socorristas s 
                    JOIN instalaciones i ON s.instalacion_id = i.id 
                    WHERE s.id = ? AND i.coordinador_id = ?
                ");
                $stmt->execute([$input['id'], $admin['id']]);
                if (!$stmt->fetch()) {
                    throw new Exception('No tienes permisos para desactivar este socorrista');
                }
            }
            
            // Si es admin, usar AdminPermissionsService para verificar permisos
            if ($admin['tipo'] === 'admin') {
                require_once __DIR__ . '/../../classes/AdminPermissionsService.php';
                $permissions = new AdminPermissionsService($admin);
                if (!$permissions->puedeAccederSocorrista($input['id'])) {
                    throw new Exception('No tienes permisos para desactivar este socorrista');
                }
            }
            
            $adminService->desactivarSocorrista($input['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Socorrista desactivado correctamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 