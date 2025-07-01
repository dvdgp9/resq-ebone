<?php
session_start();

// Configuración básica
define('APP_NAME', 'ResQ');
define('APP_VERSION', '1.0.0');

// Incluir autoloader de Composer (si existe)
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

// Incluir archivos de configuración
require_once 'config/database.php';
require_once 'config/app.php';

// Autoloader simple para clases propias
spl_autoload_register(function ($class) {
    $file = 'classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inicializar servicio de autenticación
$authService = new AuthService();

// Router básico
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remover el directorio base si existe
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $path = str_replace($basePath, '', $path);
}

// Rutas principales
switch ($path) {
    case '/':
    case '/login':
        if ($authService->estaAutenticado()) {
            header('Location: /dashboard');
            exit;
        }
        require_once 'views/login.php';
        break;
        
    case '/dashboard':
        if (!$authService->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
        require_once 'views/dashboard.php';
        break;
        
    case '/mi-cuenta':
        if (!$authService->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
        require_once 'controllers/mi_cuenta.php';
        break;
        
    case '/logout':
        require_once 'controllers/logout.php';
        break;
        
    case '/api/control-flujo':
        require_once 'controllers/control_flujo.php';
        break;
        
    case '/api/incidencias':
        require_once 'controllers/incidencias.php';
        break;
        
    case '/api/botiquin':
        require_once 'controllers/botiquin.php';
        break;
        
    case '/api/coordinador-instalacion':
        require_once 'controllers/coordinador_instalacion.php';
        break;
        
    case '/formulario/control-flujo':
        if (!$authService->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
        require_once 'views/formularios/control_flujo.php';
        break;
        
    case '/formulario/incidencias':
        if (!$authService->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
        require_once 'views/formularios/incidencias.php';
        break;
        
    case '/formulario/botiquin':
        if (!$authService->estaAutenticado()) {
            header('Location: /login');
            exit;
        }
        require_once 'views/formularios/botiquin.php';
        break;
        
    case '/admin':
    case '/admin/login':
        require_once 'views/admin/login.php';
        break;
        
    case '/admin/dashboard':
        require_once 'views/admin/dashboard.php';
        break;
        
    case '/admin/logout':
        require_once 'controllers/admin/auth.php';
        break;
        
    case '/admin/coordinadores':
        require_once 'views/admin/coordinadores.php';
        break;
        
    case '/admin/api/coordinadores':
        require_once 'controllers/admin/coordinadores.php';
        break;
        
    case '/admin/api/coordinador-instalaciones':
        require_once 'controllers/admin/coordinador_instalaciones.php';
        break;
        
    case '/admin/instalaciones':
        require_once 'views/admin/instalaciones.php';
        break;
        
    case '/admin/api/instalaciones':
        require_once 'controllers/admin/instalaciones.php';
        break;
        
    case '/admin/api/instalacion-socorristas':
        require_once 'controllers/admin/instalacion_socorristas.php';
        break;
        
    case '/admin/socorristas':
        require_once 'views/admin/socorristas.php';
        break;
        
    case '/admin/api/socorristas':
        require_once 'controllers/admin/socorristas.php';
        break;
        
    case '/admin/informes':
        require_once 'views/admin/informes.php';
        break;
        
    case '/admin/api/informes':
        require_once 'controllers/admin/informes.php';
        break;
        
    case '/debug':
        require_once 'debug_pwa.php';
        break;
        
    default:
        http_response_code(404);
        require_once 'views/404.php';
        break;
}
?> 