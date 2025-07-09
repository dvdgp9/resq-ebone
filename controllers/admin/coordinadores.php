<?php
// Controlador API para Gestión de Coordinadores
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

$admin = $adminAuth->getAdminActual();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Listar coordinadores - Solo superadmins y admins
            if ($admin['tipo'] === 'coordinador') {
                throw new Exception('Los coordinadores no tienen acceso a gestión de coordinadores');
            }
            
            $coordinadores = $adminService->getCoordinadores($admin);
            echo json_encode([
                'success' => true,
                'coordinadores' => $coordinadores
            ]);
            break;
            
        case 'POST':
            // Crear coordinador - Solo superadmins
            if ($admin['tipo'] !== 'superadmin') {
                throw new Exception('Solo superadmins pueden crear coordinadores');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            $coordinadorId = $adminService->crearCoordinador($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Coordinador creado correctamente',
                'coordinador_id' => $coordinadorId
            ]);
            break;
            
        case 'PUT':
            // Actualizar coordinador - Solo superadmins
            if ($admin['tipo'] !== 'superadmin') {
                throw new Exception('Solo superadmins pueden actualizar coordinadores');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de coordinador requerido');
            }
            
            $adminService->actualizarCoordinador($input['id'], $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Coordinador actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar coordinador - Solo superadmins
            if ($admin['tipo'] !== 'superadmin') {
                throw new Exception('Solo superadmins pueden eliminar coordinadores');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de coordinador requerido');
            }
            
            $adminService->eliminarCoordinador($input['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Coordinador eliminado correctamente'
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