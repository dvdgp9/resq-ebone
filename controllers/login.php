<?php
// Controlador de Login para ResQ
// Maneja las peticiones de autenticación

require_once 'classes/AuthService.php';

$authService = new AuthService();
$error = '';
$success = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');
    
    if (empty($dni)) {
        $error = 'Por favor, introduce tu DNI';
    } else {
        // Intentar login
        $socorrista = $authService->login($dni);
        
        if ($socorrista) {
            // Login exitoso - redirigir al dashboard
            header('Location: /dashboard');
            exit;
        } else {
            $error = 'DNI no encontrado o inactivo. Contacta con tu coordinador.';
        }
    }
}

// Si ya está autenticado, redirigir al dashboard
if ($authService->estaAutenticado()) {
    header('Location: /dashboard');
    exit;
}
?> 