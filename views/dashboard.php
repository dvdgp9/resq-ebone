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
            <div class="header-title">
                <h1>¡Bienvenida/o!</h1>
            </div>
            <div class="header-actions">
                <a href="/logout" class="btn-logout">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="forms-grid">
            <!-- Control de Flujo de Personas -->
            <h2 class="form-section-title">Control de Flujo de Personas</h2>
            <div class="form-card">
                <div class="form-card-image">
                    <img src="../assets/images/flujo-resq.png" alt="Control de Flujo" />
                </div>
                <div class="form-card-content">
                    <p class="form-card-description">
                        Registra el control de acceso y flujo de personas usuarias en las instalaciones.
                    </p>
                    <a href="/formulario/control-flujo" class="form-card-button">
                        <span>Acceder</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Incidencias -->
            <h2 class="form-section-title">Incidencias</h2>
            <div class="form-card">
                <div class="form-card-image">
                    <img src="../assets/images/incidencias-resq.png" alt="Incidencias" />
                </div>
                <div class="form-card-content">
                    <p class="form-card-description">
                        Reporta incidencias, anomalías o situaciones que requieran atención.
                    </p>
                    <a href="/formulario/incidencias" class="form-card-button">
                        <span>Acceder</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Botiquín -->
            <h2 class="form-section-title">Botiquín</h2>
            <div class="form-card">
                <div class="form-card-image">
                    <img src="../assets/images/botiquin-resq.png" alt="Botiquín" />
                </div>
                <div class="form-card-content">
                    <p class="form-card-description">
                        Gestiona el inventario del botiquín y solicita material cuando sea necesario.
                    </p>
                    <a href="/formulario/botiquin" class="form-card-button">
                        <span>Acceder</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 5L19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer-navigation.php'; ?>
</body>
</html> 