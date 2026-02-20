<?php
// Controlador de Login para ResQ
// Maneja las peticiones de autenticación

require_once 'classes/AuthService.php';

$authService = new AuthService();
$error = '';
$success = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, introduce usuario y contraseña';
    } else {
        // Intentar login
        $socorrista = $authService->login($username, $password);
        
        if ($socorrista) {
            // Login exitoso - redirigir al dashboard
            header('Location: /dashboard');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos. Contacta con tu coordinador.';
        }
    }
}

// Si ya está autenticado, redirigir al dashboard
if ($authService->estaAutenticado()) {
    header('Location: /dashboard');
    exit;
}
?> 