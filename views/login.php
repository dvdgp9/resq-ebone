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
                <label for="username">Usuario</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input form-input-center"
                    placeholder="tu.usuario"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input form-input-center"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()" title="Mostrar/ocultar contraseña">
                        <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-eye-off" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Acceder
            </button>
        </form>
        
        <div class="login-help">
            <strong>¿Problemas para acceder?</strong><br>
            Contacta con tu coordinador/a para obtener tus credenciales de acceso.
        </div>
    </div>
    
    <script>
        // Focus automático en el campo usuario
        document.getElementById('username').focus();
        
        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.querySelector('.icon-eye').style.display = 'none';
                toggleButton.querySelector('.icon-eye-off').style.display = 'inline';
                toggleButton.title = 'Ocultar contraseña';
            } else {
                passwordField.type = 'password';
                toggleButton.querySelector('.icon-eye').style.display = 'inline';
                toggleButton.querySelector('.icon-eye-off').style.display = 'none';
                toggleButton.title = 'Mostrar contraseña';
            }
        }
        
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