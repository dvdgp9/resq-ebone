<?php
// Configuración general de la aplicación ResQ

// Configuración de la aplicación
define('BASE_URL', 'https://resq.ebone.es');
define('APP_ENV', 'development'); // cambiar a 'production' en servidor

// Cargar configuración local con credenciales sensibles
// Este archivo NO se sube a Git por seguridad
$localConfigFile = __DIR__ . '/local.php';
if (file_exists($localConfigFile)) {
    require_once $localConfigFile;
} else {
    // Credenciales por defecto para desarrollo (sin valores reales)
    define('SMTP_HOST', 'localhost');
    define('SMTP_PORT', 25);
    define('SMTP_USERNAME', '');
    define('SMTP_PASSWORD', '');
    define('SMTP_SECURITY', '');
    define('SMTP_FROM_EMAIL', 'noreply@localhost');
    define('SMTP_FROM_NAME', 'ResQ - Sistema de Socorristas');
    
    // Log de advertencia
    error_log('ADVERTENCIA: config/local.php no encontrado. Copiarlo desde config/local.example.php');
}

// Configuración de sesiones
// NOTA: Estas configuraciones se mueven a cada controlador ANTES de session_start()
// para evitar warnings "Session ini settings cannot be changed when a session is active"
// ini_set('session.cookie_lifetime', 3600); // 1 hora
// ini_set('session.gc_maxlifetime', 3600);

// Configuración de errores según entorno
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Función para logging
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logFile = __DIR__ . '/../logs/app.log';
    
    // Crear directorio de logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para generar tokens seguros
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para validar DNI español
function validateDNI($dni) {
    $dni = strtoupper(trim($dni));
    
    if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
        return false;
    }
    
    $number = substr($dni, 0, 8);
    $letter = substr($dni, 8, 1);
    
    $validLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
    $expectedLetter = $validLetters[$number % 23];
    
    return $letter === $expectedLetter;
}

// Función para generar URL con versioning de assets
function assetVersion($path) {
    $fullPath = __DIR__ . '/../' . ltrim($path, '/');
    
    if (file_exists($fullPath)) {
        $lastModified = filemtime($fullPath);
        return $path . '?v=' . $lastModified;
    }
    
    return $path;
}
?> 