<?php
// Controlador API para Gestión de Administradores
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../config/database.php';

// Configurar respuesta JSON
header('Content-Type: application/json; charset=utf-8');
ob_clean();
error_reporting(0);

$adminAuth = new AdminAuthService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado como administrador']);
    exit;
}

$admin = $adminAuth->getAdminActual();
$method = $_SERVER['REQUEST_METHOD'];

// Verificar que sea superadmin para gestión de administradores
if ($admin['tipo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo superadmins pueden gestionar administradores']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            // Listar administradores
            $stmt = $db->prepare("
                SELECT id, email, nombre, telefono, tipo, activo, fecha_creacion, fecha_actualizacion 
                FROM admins 
                ORDER BY tipo ASC, nombre ASC
            ");
            $stmt->execute();
            $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'administradores' => $administradores
            ]);
            break;
            
        case 'POST':
            // Crear administrador
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos JSON inválidos');
            }
            
            // Validaciones
            if (empty($input['email']) || empty($input['nombre']) || empty($input['tipo']) || empty($input['password'])) {
                throw new Exception('Email, nombre, tipo y password son obligatorios');
            }
            
            if (!in_array($input['tipo'], ['superadmin', 'admin', 'coordinador'])) {
                throw new Exception('Tipo de administrador inválido');
            }
            
            // Verificar email único
            $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetch()) {
                throw new Exception('Ya existe un administrador con ese email');
            }
            
            // Hash del password
            $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            // Crear administrador
            $stmt = $db->prepare("
                INSERT INTO admins (email, password_hash, nombre, telefono, tipo, activo, fecha_creacion, fecha_actualizacion) 
                VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $input['email'],
                $passwordHash,
                $input['nombre'],
                $input['telefono'] ?? null,
                $input['tipo']
            ]);
            
            $adminId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Administrador creado correctamente',
                'admin_id' => $adminId
            ]);
            break;
            
        case 'PUT':
            // Actualizar administrador
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de administrador requerido');
            }
            
            // Validaciones
            if (empty($input['email']) || empty($input['nombre']) || empty($input['tipo'])) {
                throw new Exception('Email, nombre y tipo son obligatorios');
            }
            
            if (!in_array($input['tipo'], ['superadmin', 'admin', 'coordinador'])) {
                throw new Exception('Tipo de administrador inválido');
            }
            
            // Verificar email único (excluyendo el propio admin)
            $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$input['email'], $input['id']]);
            if ($stmt->fetch()) {
                throw new Exception('Ya existe un administrador con ese email');
            }
            
            // Verificar que el admin existe
            $stmt = $db->prepare("SELECT id FROM admins WHERE id = ?");
            $stmt->execute([$input['id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Administrador no encontrado');
            }
            
            // Actualizar administrador
            if (!empty($input['password'])) {
                // Actualizar con nueva contraseña
                $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE admins 
                    SET email = ?, password_hash = ?, nombre = ?, telefono = ?, tipo = ?, activo = ?, fecha_actualizacion = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['email'],
                    $passwordHash,
                    $input['nombre'],
                    $input['telefono'] ?? null,
                    $input['tipo'],
                    $input['activo'] ?? 1,
                    $input['id']
                ]);
            } else {
                // Actualizar sin cambiar contraseña
                $stmt = $db->prepare("
                    UPDATE admins 
                    SET email = ?, nombre = ?, telefono = ?, tipo = ?, activo = ?, fecha_actualizacion = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['email'],
                    $input['nombre'],
                    $input['telefono'] ?? null,
                    $input['tipo'],
                    $input['activo'] ?? 1,
                    $input['id']
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Administrador actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar administrador
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de administrador requerido');
            }
            
            // No permitir que el superadmin se elimine a sí mismo
            if ($input['id'] == $admin['id']) {
                throw new Exception('No puedes eliminarte a ti mismo');
            }
            
            // Verificar que el admin existe
            $stmt = $db->prepare("SELECT id, tipo FROM admins WHERE id = ?");
            $stmt->execute([$input['id']]);
            $adminAEliminar = $stmt->fetch();
            
            if (!$adminAEliminar) {
                throw new Exception('Administrador no encontrado');
            }
            
            // Marcar como inactivo en lugar de eliminar
            $stmt = $db->prepare("UPDATE admins SET activo = 0, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->execute([$input['id']]);
            
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