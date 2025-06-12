<?php
// Controlador de Logout para ResQ
// Maneja el cierre de sesión

require_once 'classes/AuthService.php';

$authService = new AuthService();

// Cerrar sesión
$authService->logout();

// Redirigir al login con mensaje
header('Location: /login?logout=1');
exit;
?> 