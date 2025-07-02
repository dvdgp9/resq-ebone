<?php
// Controlador API para Gestión de Administradores
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

// Solo Superadmin puede gestionar administradores
if ($admin['tipo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo Superadmin puede gestionar administradores']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Listar administradores
            $administradores = $adminService->getAdministradores();
            echo json_encode([
                'success' => true,
                'administradores' => $administradores
            ]);
            break;
            
        case 'POST':
            // Crear/Actualizar administrador
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            // Validaciones básicas
            if (empty($input['nombre'])) {
                throw new Exception('El nombre es requerido');
            }
            
            if (empty($input['email'])) {
                throw new Exception('El email es requerido');
            }
            
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            if (empty($input['tipo']) || !in_array($input['tipo'], ['coordinador', 'superadmin'])) {
                throw new Exception('Tipo de administrador inválido');
            }
            
            // Si es creación, validar password
            if (empty($input['id']) && empty($input['password'])) {
                throw new Exception('Password es requerido para nuevos administradores');
            }
            
            // Validar password si se proporciona
            if (!empty($input['password']) && strlen($input['password']) < 8) {
                throw new Exception('Password debe tener al menos 8 caracteres');
            }
            
            // Validar que tiene mayúscula y minúscula
            if (!empty($input['password']) && (!preg_match('/[A-Z]/', $input['password']) || !preg_match('/[a-z]/', $input['password']))) {
                throw new Exception('Password debe contener al menos una mayúscula y una minúscula');
            }
            
            if (empty($input['id'])) {
                // Crear administrador
                $adminId = $adminService->crearAdministrador($input);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Administrador creado correctamente',
                    'administrador_id' => $adminId
                ]);
            } else {
                // Actualizar administrador
                
                // Prevenir que el admin se desactive a sí mismo
                if ($input['id'] == $admin['id'] && isset($input['activo']) && !$input['activo']) {
                    throw new Exception('No puedes desactivar tu propia cuenta');
                }
                
                $adminService->actualizarAdministrador($input['id'], $input);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Administrador actualizado correctamente'
                ]);
            }
            break;
            
        case 'DELETE':
            // Desactivar administrador
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de administrador requerido');
            }
            
            // Prevenir auto-desactivación
            if ($input['id'] == $admin['id']) {
                throw new Exception('No puedes desactivar tu propia cuenta');
            }
            
            $adminService->desactivarAdministrador($input['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Administrador desactivado correctamente'
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