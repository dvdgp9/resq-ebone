<?php
// Controlador API para Gestión de Instalaciones
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
            // Listar instalaciones
            $instalaciones = $adminService->getInstalaciones();
            echo json_encode([
                'success' => true,
                'instalaciones' => $instalaciones
            ]);
            break;
            
        case 'POST':
            // Crear instalación
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            $instalacionId = $adminService->crearInstalacion($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Instalación creada correctamente',
                'instalacion_id' => $instalacionId
            ]);
            break;
            
        case 'PUT':
            // Actualizar instalación
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de instalación requerido');
            }
            
            $adminService->actualizarInstalacion($input['id'], $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Instalación actualizada correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar instalación
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de instalación requerido');
            }
            
            $adminService->eliminarInstalacion($input['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Instalación eliminada correctamente'
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