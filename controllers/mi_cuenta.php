<?php
// Controlador para Mi Cuenta
// Muestra información personal del socorrista

require_once 'config/app.php';
require_once 'classes/AuthService.php';

$authService = new AuthService();

// Verificar autenticación
if (!$authService->estaAutenticado()) {
    header('Location: /login');
    exit;
}

// Obtener información completa del socorrista
$socorrista = $authService->getSocorristaActual();

try {
    $db = getDB();
    
    // Obtener información completa del socorrista con instalaciones
    $stmt = $db->prepare("
        SELECT s.id, s.dni, s.nombre, s.email, s.telefono,
               i.nombre as instalacion_nombre, i.direccion as instalacion_direccion,
               c.nombre as coordinador_nombre, c.email as coordinador_email, c.telefono as coordinador_telefono
        FROM socorristas s
        JOIN instalaciones i ON s.instalacion_id = i.id
        JOIN admins c ON i.coordinador_id = c.id
        WHERE s.id = ? AND c.tipo = 'coordinador'
    ");
    
    $stmt->execute([$socorrista['id']]);
    $socorristaCompleto = $stmt->fetch();
    
    if (!$socorristaCompleto) {
        throw new Exception('No se pudo obtener la información del socorrista');
    }
    
    // Obtener todas las instalaciones asignadas al socorrista (si hubiera múltiples)
    $stmt = $db->prepare("
        SELECT DISTINCT i.nombre, i.direccion
        FROM instalaciones i
        JOIN socorristas s ON s.instalacion_id = i.id
        WHERE s.id = ? AND i.activo = 1
    ");
    
    $stmt->execute([$socorrista['id']]);
    $instalaciones = $stmt->fetchAll();
    
} catch (Exception $e) {
    logMessage("Error obteniendo información del socorrista: " . $e->getMessage(), 'ERROR');
    $error = "Error al cargar la información del perfil";
}

// Cargar la vista
require_once 'views/mi_cuenta.php';
?> 