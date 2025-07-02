<?php
// Controlador para gestión de administradores (solo Superadmin)
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/AdminAuthService.php';

// Verificar autenticación admin
$adminAuth = new AdminAuthService();

// DEBUG TEMPORAL - Añadir información de sesión
if (!$adminAuth->estaAutenticadoAdmin()) {
    http_response_code(401);
    echo json_encode([
        'error' => 'No autenticado',
        'debug' => [
            'session_admin_id' => $_SESSION['admin_id'] ?? 'NO EXISTE',
            'session_admin_session_id' => $_SESSION['admin_session_id'] ?? 'NO EXISTE',
            'cookies' => $_COOKIE,
            'session_status' => session_status(),
            'session_id' => session_id()
        ]
    ]);
    exit;
}

$admin = $adminAuth->getAdminActual();

// Verificar que es Superadmin
if (!$adminAuth->esSuperAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo los Superadmin pueden gestionar administradores']);
    exit;
}

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
                case 'listar':
                    listarAdministradores();
                    break;
                case 'coordinadores':
                    getCoordinadores();
                    break;
                case 'administrador':
                    getAdministrador();
                    break;
                default:
                    listarAdministradores();
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'crear':
                    crearAdministrador($admin);
                    break;
                case 'actualizar':
                    actualizarAdministrador($admin);
                    break;
                case 'desactivar':
                    desactivarAdministrador($admin);
                    break;
                case 'asignar_coordinadores':
                    asignarCoordinadores($admin);
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

// Función para listar administradores
function listarAdministradores() {
    $db = Database::getInstance()->getConnection();
    
    // Obtener administradores con coordinadores asignados
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.email,
            a.nombre,
            a.tipo,
            a.activo,
            a.fecha_creacion,
            a.fecha_actualizacion,
            GROUP_CONCAT(c.nombre SEPARATOR ', ') as coordinadores_asignados,
            COUNT(ac.coordinador_id) as total_coordinadores
        FROM admins a
        LEFT JOIN admin_coordinadores ac ON a.id = ac.admin_id AND ac.activo = 1
        LEFT JOIN coordinadores c ON ac.coordinador_id = c.id
        GROUP BY a.id
        ORDER BY a.fecha_creacion DESC
    ");
    
    $stmt->execute();
    $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'administradores' => $administradores
    ]);
}

// Función para obtener coordinadores disponibles
function getCoordinadores() {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT id, nombre, email 
        FROM coordinadores 
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $coordinadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'coordinadores' => $coordinadores
    ]);
}

// Función para obtener un administrador específico
function getAdministrador() {
    $adminId = $_GET['id'] ?? null;
    
    if (!$adminId) {
        throw new Exception('ID del administrador requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener datos del administrador
    $stmt = $db->prepare("
        SELECT id, email, nombre, tipo, activo, fecha_creacion, fecha_actualizacion
        FROM admins 
        WHERE id = ?
    ");
    
    $stmt->execute([$adminId]);
    $administrador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$administrador) {
        throw new Exception('Administrador no encontrado');
    }
    
    // Obtener coordinadores asignados
    $stmt = $db->prepare("
        SELECT c.id, c.nombre, c.email
        FROM coordinadores c
        INNER JOIN admin_coordinadores ac ON c.id = ac.coordinador_id
        WHERE ac.admin_id = ? AND ac.activo = 1
        ORDER BY c.nombre ASC
    ");
    
    $stmt->execute([$adminId]);
    $coordinadores_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $administrador['coordinadores_asignados'] = $coordinadores_asignados;
    
    echo json_encode([
        'success' => true,
        'administrador' => $administrador
    ]);
}

// Función para crear administrador
function crearAdministrador($admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $required = ['email', 'nombre', 'password', 'tipo'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || trim($input[$field]) === '') {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Validar email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email no válido');
    }
    
    // Validar tipo
    if (!in_array($input['tipo'], ['admin', 'superadmin'])) {
        throw new Exception('Tipo de administrador no válido');
    }
    
    // Validar password
    if (strlen($input['password']) < 8) {
        throw new Exception('El password debe tener al menos 8 caracteres');
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])/', $input['password'])) {
        throw new Exception('El password debe contener al menos una mayúscula y una minúscula');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar email único
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([trim($input['email'])]);
    
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un administrador con ese email');
    }
    
    // Crear administrador
    $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO admins 
        (email, nombre, password_hash, tipo, activo)
        VALUES (?, ?, ?, ?, 1)
    ");
    
    $stmt->execute([
        trim($input['email']),
        trim($input['nombre']),
        $passwordHash,
        $input['tipo']
    ]);
    
    $adminId = $db->lastInsertId();
    
    // Asignar coordinadores si es tipo 'admin'
    if ($input['tipo'] === 'admin' && !empty($input['coordinadores'])) {
        asignarCoordinadoresAlAdmin($adminId, $input['coordinadores']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Administrador creado correctamente',
        'admin_id' => $adminId
    ]);
}

// Función para actualizar administrador
function actualizarAdministrador($admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('ID del administrador requerido');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener administrador actual
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$input['id']]);
    $adminActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminActual) {
        throw new Exception('Administrador no encontrado');
    }
    
    // Validar campos
    if (isset($input['nombre']) && trim($input['nombre']) === '') {
        throw new Exception('El nombre no puede estar vacío');
    }
    
    if (isset($input['tipo']) && !in_array($input['tipo'], ['admin', 'superadmin'])) {
        throw new Exception('Tipo de administrador no válido');
    }
    
    // Construir query de actualización dinámicamente
    $updates = [];
    $params = [];
    
    if (isset($input['nombre'])) {
        $updates[] = "nombre = ?";
        $params[] = trim($input['nombre']);
    }
    
    if (isset($input['tipo'])) {
        $updates[] = "tipo = ?";
        $params[] = $input['tipo'];
    }
    
    if (isset($input['password']) && !empty($input['password'])) {
        // Validar nuevo password
        if (strlen($input['password']) < 8) {
            throw new Exception('El password debe tener al menos 8 caracteres');
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])/', $input['password'])) {
            throw new Exception('El password debe contener al menos una mayúscula y una minúscula');
        }
        
        $updates[] = "password_hash = ?";
        $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    if (!empty($updates)) {
        $updates[] = "fecha_actualizacion = NOW()";
        $params[] = $input['id'];
        
        $sql = "UPDATE admins SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }
    
    // Actualizar coordinadores asignados si es tipo 'admin'
    if (isset($input['coordinadores']) && ($adminActual['tipo'] === 'admin' || $input['tipo'] === 'admin')) {
        // Desactivar asignaciones actuales
        $stmt = $db->prepare("UPDATE admin_coordinadores SET activo = 0 WHERE admin_id = ?");
        $stmt->execute([$input['id']]);
        
        // Asignar nuevos coordinadores
        if (!empty($input['coordinadores'])) {
            asignarCoordinadoresAlAdmin($input['id'], $input['coordinadores']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Administrador actualizado correctamente'
    ]);
}

// Función para desactivar administrador
function desactivarAdministrador($admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        throw new Exception('ID del administrador requerido');
    }
    
    // No permitir que se desactive a sí mismo
    if ($input['id'] == $admin['id']) {
        throw new Exception('No puedes desactivar tu propia cuenta');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar que existe
    $stmt = $db->prepare("SELECT id FROM admins WHERE id = ?");
    $stmt->execute([$input['id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Administrador no encontrado');
    }
    
    // Desactivar administrador
    $stmt = $db->prepare("
        UPDATE admins 
        SET activo = 0, fecha_actualizacion = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$input['id']]);
    
    // Desactivar asignaciones de coordinadores
    $stmt = $db->prepare("UPDATE admin_coordinadores SET activo = 0 WHERE admin_id = ?");
    $stmt->execute([$input['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Administrador desactivado correctamente'
    ]);
}

// Función auxiliar para asignar coordinadores a un admin
function asignarCoordinadoresAlAdmin($adminId, $coordinadores) {
    $db = Database::getInstance()->getConnection();
    
    foreach ($coordinadores as $coordinadorId) {
        // Verificar que el coordinador existe
        $stmt = $db->prepare("SELECT id FROM coordinadores WHERE id = ?");
        $stmt->execute([$coordinadorId]);
        
        if ($stmt->fetch()) {
            // Insertar o reactivar asignación
            $stmt = $db->prepare("
                INSERT INTO admin_coordinadores (admin_id, coordinador_id, activo)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE activo = 1, fecha_asignacion = NOW()
            ");
            
            $stmt->execute([$adminId, $coordinadorId]);
        }
    }
}

// Función para gestionar asignaciones de coordinadores
function asignarCoordinadores($admin) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['admin_id']) || !isset($input['coordinadores'])) {
        throw new Exception('ID del admin y coordinadores son requeridos');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Verificar que el admin existe
    $stmt = $db->prepare("SELECT id FROM admins WHERE id = ?");
    $stmt->execute([$input['admin_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Administrador no encontrado');
    }
    
    // Desactivar asignaciones actuales
    $stmt = $db->prepare("UPDATE admin_coordinadores SET activo = 0 WHERE admin_id = ?");
    $stmt->execute([$input['admin_id']]);
    
    // Asignar nuevos coordinadores
    if (!empty($input['coordinadores'])) {
        asignarCoordinadoresAlAdmin($input['admin_id'], $input['coordinadores']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Coordinadores asignados correctamente'
    ]);
}
?> 