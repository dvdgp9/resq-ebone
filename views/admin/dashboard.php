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
            <div class="header-title">
                <h1>🎛️ Panel de Administración</h1>
            </div>
            <div class="header-actions">
                <span class="admin-badge"><?= $admin['tipo'] === 'superadmin' ? 'Super Admin' : ($admin['tipo'] === 'admin' ? 'Admin' : 'Coordinador') ?></span>
                <a href="/admin/logout" class="btn-logout">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>
    
    <div class="container admin-container">
        
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
            
            <?php if ($admin['tipo'] !== 'coordinador'): ?>
            <a href="/admin/coordinadores" class="admin-nav-card">
                <div class="nav-card-icon">👥</div>
                <h3>Gestionar Coordinadores</h3>
                <p>Crear, editar y gestionar coordinadores del sistema</p>
                <div class="nav-card-arrow">→</div>
            </a>
            <?php endif; ?>
            
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

    </div>
    

</body>
</html> 