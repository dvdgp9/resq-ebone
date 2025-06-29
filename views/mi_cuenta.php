<?php
// Vista de Mi Cuenta para ResQ
// Página de información personal del socorrista
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResQ - Mi Cuenta</title>
    
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
                <h1>Mi Cuenta</h1>
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
        <?php if (isset($error)): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php else: ?>
            <div class="profile-section">
                <!-- Información Personal -->
                <h2 class="form-section-title">Información Personal</h2>
                <div class="profile-card">
                    <div class="profile-avatar">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="profile-info">
                        <div class="profile-field">
                            <label>Nombre Completo</label>
                            <p><?= htmlspecialchars($socorristaCompleto['nombre']) ?></p>
                        </div>
                        <div class="profile-field">
                            <label>DNI</label>
                            <p><?= htmlspecialchars($socorristaCompleto['dni']) ?></p>
                        </div>
                        <?php if ($socorristaCompleto['email']): ?>
                        <div class="profile-field">
                            <label>Email</label>
                            <p><?= htmlspecialchars($socorristaCompleto['email']) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($socorristaCompleto['telefono']): ?>
                        <div class="profile-field">
                            <label>Teléfono</label>
                            <p><?= htmlspecialchars($socorristaCompleto['telefono']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Instalación Asignada -->
                <h2 class="form-section-title">Instalación Asignada</h2>
                <div class="profile-card">
                    <div class="profile-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="profile-info">
                        <div class="profile-field">
                            <label>Instalación</label>
                            <p><?= htmlspecialchars($socorristaCompleto['instalacion_nombre']) ?></p>
                        </div>
                        <?php if ($socorristaCompleto['instalacion_direccion']): ?>
                        <div class="profile-field">
                            <label>Dirección</label>
                            <p><?= htmlspecialchars($socorristaCompleto['instalacion_direccion']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Coordinador -->
                <h2 class="form-section-title">Coordinador</h2>
                <div class="profile-card">
                    <div class="profile-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="8.5" cy="7" r="4" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 8V14" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 11H17" stroke="#D33E22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="profile-info">
                        <div class="profile-field">
                            <label>Nombre</label>
                            <p><?= htmlspecialchars($socorristaCompleto['coordinador_nombre']) ?></p>
                        </div>
                        <?php if ($socorristaCompleto['coordinador_email']): ?>
                        <div class="profile-field">
                            <label>Email</label>
                            <p><a href="mailto:<?= htmlspecialchars($socorristaCompleto['coordinador_email']) ?>"><?= htmlspecialchars($socorristaCompleto['coordinador_email']) ?></a></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($socorristaCompleto['coordinador_telefono']): ?>
                        <div class="profile-field">
                            <label>Teléfono</label>
                            <p><a href="tel:<?= htmlspecialchars($socorristaCompleto['coordinador_telefono']) ?>"><?= htmlspecialchars($socorristaCompleto['coordinador_telefono']) ?></a></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer de Navegación -->
    <footer class="nav-footer">
        <div class="nav-footer-container">
            <button class="nav-item" data-nav="inicio">
                <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="nav-label">Inicio</span>
            </button>
            
            <button class="nav-item" data-nav="formularios" onclick="openFormularios()">
                <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 13H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 17H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 9H9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="nav-label">Formularios</span>
            </button>
            
            <button class="nav-item active" data-nav="cuenta">
                <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="nav-label">Mi cuenta</span>
            </button>
        </div>
    </footer>

    <!-- Modal Formularios -->
    <div class="modal-overlay" id="formulariosModal" onclick="closeFormularios()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Seleccionar Formulario</h3>
                <button class="modal-close" onclick="closeFormularios()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <a href="/formulario/control-flujo" class="modal-form-option">
                    <div class="modal-form-icon">
                        <img src="../assets/images/flujo-resq.png" alt="Control de Flujo" />
                    </div>
                    <div class="modal-form-content">
                        <h4>Control de Flujo</h4>
                        <p>Registra el control de acceso y flujo de personas</p>
                    </div>
                </a>
                
                <a href="/formulario/incidencias" class="modal-form-option">
                    <div class="modal-form-icon">
                        <img src="../assets/images/incidencias-resq.png" alt="Incidencias" />
                    </div>
                    <div class="modal-form-content">
                        <h4>Incidencias</h4>
                        <p>Reporta incidencias y situaciones de atención</p>
                    </div>
                </a>
                
                <a href="/formulario/botiquin" class="modal-form-option">
                    <div class="modal-form-icon">
                        <img src="../assets/images/botiquin-resq.png" alt="Botiquín" />
                    </div>
                    <div class="modal-form-content">
                        <h4>Botiquín</h4>
                        <p>Gestiona el inventario del botiquín</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        function openFormularios() {
            document.getElementById('formulariosModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeFormularios() {
            document.getElementById('formulariosModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Navegación footer
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.dataset.nav === 'inicio') {
                    window.location.href = '/dashboard';
                } else if (this.dataset.nav === 'cuenta') {
                    window.location.href = '/mi-cuenta';
                } else if (this.dataset.nav !== 'formularios') {
                    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>