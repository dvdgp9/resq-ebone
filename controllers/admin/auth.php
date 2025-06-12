<?php
// Controlador de Autenticación Admin para ResQ
// Maneja login y logout del panel de administración

require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();
$error = '';
$success = '';

// Procesar según el método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Login
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, introduce email y contraseña';
    } else {
        // Intentar login
        $admin = $adminAuth->login($email, $password);
        
        if ($admin) {
            // Login exitoso - redirigir al dashboard admin
            header('Location: /admin/dashboard');
            exit;
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
    
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/admin/logout') {
    // Logout
    $adminAuth->logout();
    header('Location: /admin?logout=1');
    exit;
}

// Si ya está autenticado, redirigir al dashboard
if ($adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin/dashboard');
    exit;
}
?> 