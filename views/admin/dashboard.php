<?php
// Vista del Dashboard Admin para ResQ
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';

$adminAuth = new AdminAuthService();
$adminService = new AdminService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

// Obtener datos del admin actual y estadísticas
$admin = $adminAuth->getAdminActual();
$stats = $adminService->getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="dashboard-page">
    <header class="header admin-header">
        <div class="header-content">
            <div class="logo">
                <img src="/assets/images/logo.png" alt="ResQ Logo" class="header-logo">
            </div>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($admin['nombre']) ?></span>
                <span class="admin-badge"><?= $admin['tipo'] === 'superadmin' ? 'Super Admin' : 'Coordinador' ?></span>
                <a href="/admin/logout" class="btn btn-secondary btn-small">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container admin-container">
        <div class="admin-welcome">
            <h1>🎛️ Panel de Administración</h1>
            <p>Bienvenido, <?= htmlspecialchars($admin['nombre']) ?>. Gestiona coordinadores, instalaciones y socorristas desde aquí.</p>
        </div>
        
        <!-- Cards de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>Coordinadores</h3>
                    <div class="stat-number"><?= $stats['coordinadores'] ?? 0 ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <h3>Instalaciones</h3>
                    <div class="stat-number"><?= $stats['instalaciones'] ?? 0 ?></div>
                    <div class="stat-label">Activas</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚑</div>
                <div class="stat-content">
                    <h3>Socorristas</h3>
                    <div class="stat-number"><?= $stats['socorristas'] ?? 0 ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3>Formularios</h3>
                    <div class="stat-number"><?= $stats['formularios_mes'] ?? 0 ?></div>
                    <div class="stat-label">Este mes</div>
                </div>
            </div>
        </div>
        
        <!-- Navegación Principal -->
        <div class="admin-nav-grid">
            <?php if ($admin['tipo'] === 'superadmin'): ?>
            <a href="/admin/administradores" class="admin-nav-card">
                <div class="nav-card-icon">🛡️</div>
                <h3>Gestión Admin</h3>
                <p>Crear y gestionar administradores del sistema</p>
                <div class="nav-card-arrow">→</div>
            </a>
            <?php endif; ?>
            
            <a href="/admin/coordinadores" class="admin-nav-card">
                <div class="nav-card-icon">👥</div>
                <h3>Gestionar Coordinadores</h3>
                <p>Crear, editar y gestionar coordinadores del sistema</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/instalaciones" class="admin-nav-card">
                <div class="nav-card-icon">🏢</div>
                <h3>Gestionar Instalaciones</h3>
                <p>Administrar instalaciones y asignar coordinadores</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/socorristas" class="admin-nav-card">
                <div class="nav-card-icon">🚑</div>
                <h3>Gestionar Socorristas</h3>
                <p>Crear socorristas y asignarlos a instalaciones</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/informes" class="admin-nav-card">
                <div class="nav-card-icon">📊</div>
                <h3>Informes y Exportación</h3>
                <p>Exportar formularios a Excel con filtros personalizados</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/botiquin" class="admin-nav-card">
                <div class="nav-card-icon">🏥</div>
                <h3>Gestión de Botiquín</h3>
                <p>Administrar inventarios de botiquín y solicitudes de material</p>
                <div class="nav-card-arrow">→</div>
            </a>
        </div>
        
        <!-- Accesos Rápidos -->
        <div class="quick-actions">
            <h2>⚡ Acciones Rápidas</h2>
            <div class="quick-actions-grid">
                <?php if ($admin['tipo'] === 'superadmin'): ?>
                <button class="btn btn-primary" onclick="window.location.href='/admin/administradores?action=create'">
                    🛡️ Nuevo Admin
                </button>
                <?php endif; ?>
                <button class="btn btn-primary" onclick="window.location.href='/admin/coordinadores?action=create'">
                    ➕ Nuevo Coordinador
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/instalaciones?action=create'">
                    🏢 Nueva Instalación
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/socorristas?action=create'">
                    🚑 Nuevo Socorrista
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/botiquin'">
                    🏥 Gestión Botiquín
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='/'">
                    🚨 Ir a Panel Socorristas
                </button>
            </div>
        </div>
    </div>
    

</body>
</html> 