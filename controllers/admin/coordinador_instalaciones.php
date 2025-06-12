<?php
// API para obtener instalaciones de un coordinador específico
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

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Obtener ID del coordinador
        $coordinadorId = $_GET['coordinador_id'] ?? null;
        
        if (!$coordinadorId) {
            throw new Exception('ID de coordinador requerido');
        }
        
        // Obtener instalaciones del coordinador
        $instalaciones = $adminService->getInstalacionesPorCoordinador($coordinadorId);
        
        echo json_encode([
            'success' => true,
            'instalaciones' => $instalaciones
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 