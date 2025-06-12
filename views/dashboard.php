<?php
// Vista del Dashboard para ResQ
// Página principal después del login

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
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="dashboard-page">
    <header class="header">
        <div class="header-content">
            <div class="logo">
                🚨 ResQ
            </div>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($socorrista['nombre']) ?></span>
                <a href="/logout" class="btn btn-secondary btn-small">Cerrar Sesión</a>
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
            
            <!-- Parte de Accidente -->
            <div class="form-card" onclick="location.href='/formulario/parte-accidente'">
                <div class="form-icon">🚨</div>
                <h2 class="form-title">Parte de Accidente</h2>
                <p class="form-description">
                    Documenta accidentes laborales o emergencias médicas ocurridas.
                </p>
                <a href="/formulario/parte-accidente" class="btn btn-primary">Acceder</a>
            </div>
        </div>
    </div>
</body>
</html> 