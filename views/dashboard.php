<?php
// Vista del Dashboard para ResQ
// Página principal después del login

require_once 'config/app.php';
require_once 'classes/AuthService.php';

$authService = new AuthService();

// Verificar autenticación
if (!$authService->estaAutenticado()) {
    header('Location: /login');
    exit;
}

// Obtener datos del socorrista actual
$socorrista = $authService->getSocorristaActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResQ - Dashboard</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistema de gestión para socorristas y salvavidas">
    <meta name="theme-color" content="#D33E22">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ResQ">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/assets/images/logo.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/images/logo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/logo.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/assets/images/logo.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/logo.png">
    
    <link rel="stylesheet" href="<?= assetVersion('/assets/css/styles.css') ?>">
</head>
<body class="dashboard-page">
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="/assets/images/logo-negativo-soco.png" alt="ResQ Logo" class="header-logo">
            </div>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($socorrista['nombre']) ?></span>
                <span>🏢 <?= htmlspecialchars($socorrista['instalacion_nombre']) ?></span>
                <a href="/logout" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="card">
            <h1 class="card-title">¡Bienvenida/o, <?= htmlspecialchars($socorrista['nombre']) ?>!</h1>
            <p class="card-subtitle">Selecciona el formulario que necesitas completar</p>
            
            <div class="user-details">
                <strong>📍 Instalación:</strong> <?= htmlspecialchars($socorrista['instalacion_nombre']) ?><br>
                <strong>🆔 DNI:</strong> <?= htmlspecialchars($socorrista['dni']) ?><br>
                <strong>🕐 Sesión iniciada:</strong> <?= date('d/m/Y H:i', $socorrista['login_time']) ?>
            </div>
        </div>
        
        <div class="forms-grid">
            <!-- Control de Flujo de Personas -->
            <div class="form-card" onclick="location.href='/formulario/control-flujo'">
                <div class="form-icon">👥</div>
                <h2 class="form-title">Control de Flujo de Personas</h2>
                <p class="form-description">
                    Registra el control de acceso y flujo de personas usuarias en las instalaciones.
                </p>
                <a href="/formulario/control-flujo" class="btn btn-primary">Acceder</a>
            </div>
            
            <!-- Incidencias -->
            <div class="form-card" onclick="location.href='/formulario/incidencias'">
                <div class="form-icon">⚠️</div>
                <h2 class="form-title">Incidencias</h2>
                <p class="form-description">
                    Reporta incidencias, anomalías o situaciones que requieran atención.
                </p>
                <a href="/formulario/incidencias" class="btn btn-primary">Acceder</a>
            </div>
            
            <!-- Botiquín -->
            <div class="form-card" onclick="location.href='/formulario/botiquin'">
                <div class="form-icon">🏥</div>
                <h2 class="form-title">Botiquín</h2>
                <p class="form-description">
                    Gestiona el inventario del botiquín y solicita material cuando sea necesario.
                </p>
                <a href="/formulario/botiquin" class="btn btn-primary">Acceder</a>
            </div>
        </div>
    </div>

    <script>
        // Dashboard script placeholder
    </script>
</body>
</html> 