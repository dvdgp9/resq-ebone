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

$admin = $adminAuth->getAdminActual();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Listar socorristas
            $socorristas = $adminService->getSocorristas();
            echo json_encode([
                'success' => true,
                'socorristas' => $socorristas
            ]);
            break;
            
        case 'POST':
            // Crear socorrista
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            $socorristaId = $adminService->crearSocorrista($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Socorrista creado correctamente',
                'socorrista_id' => $socorristaId
            ]);
            break;
            
        case 'PUT':
            // Actualizar socorrista
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de socorrista requerido');
            }
            
            $adminService->actualizarSocorrista($input['id'], $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Socorrista actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar socorrista
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de socorrista requerido');
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