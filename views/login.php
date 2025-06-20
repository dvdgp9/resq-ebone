<?php
// Vista de Login para ResQ
require_once 'config/app.php';
require_once 'controllers/login.php';

// Mensaje de logout
$logoutMessage = '';
if (isset($_GET['logout'])) {
    $logoutMessage = 'Sesión cerrada correctamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResQ - Acceso Socorristas</title>
    
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
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <img src="/assets/images/logo.png" alt="ResQ Logo" class="logo-image">
        </div>
        <p class="login-subtitle">Socorrismo Grupo Ebone</p>
        
        <?php if ($logoutMessage): ?>
            <div class="message message-success"><?= htmlspecialchars($logoutMessage) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label for="dni">DNI</label>
                <input 
                    type="text" 
                    id="dni" 
                    name="dni" 
                    class="form-input form-input-center"
                    placeholder="12345678Z"
                    maxlength="9"
                    required
                    autocomplete="off"
                    value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
                >
                <div class="form-help">Formato: 8 números + letra (ej: 12345678Z)</div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                🔐 Acceder
            </button>
        </form>
        
        <div class="login-help">
            <strong>¿Problemas para acceder?</strong><br>
            Contacta con tu coordinador/a para verificar que tu DNI esté registrado en el sistema.
        </div>
    </div>
    
    <script>
        // Auto-formatear DNI mientras se escribe
        document.getElementById('dni').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^0-9A-Z]/g, '');
            
            // Limitar a 9 caracteres
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            
            e.target.value = value;
        });
        
        // Focus automático en el campo DNI
        document.getElementById('dni').focus();
        
        // Registrar Service Worker para PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registrado con éxito: ', registration);
                        
                        // Manejar actualizaciones del Service Worker
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            console.log('Nueva versión del SW encontrada');
                            
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed') {
                                    if (navigator.serviceWorker.controller) {
                                        console.log('Nueva versión del SW instalada, actualizando...');
                                        // Notificar al nuevo SW que tome control
                                        newWorker.postMessage({action: 'skipWaiting'});
                                    } else {
                                        console.log('SW instalado por primera vez');
                                    }
                                }
                            });
                        });
                        
                        // Escuchar cuando el SW toma control
                        navigator.serviceWorker.addEventListener('controllerchange', () => {
                            console.log('SW ha tomado control, recargando página...');
                            window.location.reload();
                        });
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
        
        // Mostrar prompt de instalación de PWA
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevenir que Chrome 67 y versiones anteriores muestren automáticamente el prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            
            // Mostrar un botón de instalación personalizado si se desea
            // showInstallPromotion();
        });
        
        window.addEventListener('appinstalled', (evt) => {
            console.log('PWA was installed');
        });
    </script>
</body>
</html> 