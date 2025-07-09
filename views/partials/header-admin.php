<?php
/**
 * Header Universal para ResQ - Administradores
 * Componente reutilizable para todas las páginas de administradores
 * 
 * Parámetros esperados:
 * - $pageTitle: Título de la página administrativa
 * - $admin: Array con datos del administrador actual
 */

// Función helper para mostrar el tipo de admin correctamente
function getTipoAdminBadge($tipo) {
    switch($tipo) {
        case 'superadmin':
            return 'Super Admin';
        case 'admin':
            return 'Admin';
        case 'coordinador':
            return 'Coordinador';
        default:
            return 'Usuario';
    }
}
?>
<header class="header admin-header">
    <div class="header-content">
        <div class="logo">
            <img src="/assets/images/logo.png" alt="ResQ Logo" class="header-logo">
        </div>
        <div class="header-title">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
        <div class="header-actions">
            <span class="admin-badge"><?= getTipoAdminBadge($admin['tipo']) ?></span>
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