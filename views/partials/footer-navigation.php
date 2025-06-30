<?php
/**
 * Footer de navegación para socorristas
 * Incluye navegación móvil, modal de formularios y JavaScript
 */

// Determinar página actual para estado activo
$current_page = $_SERVER['REQUEST_URI'] ?? '';
$is_dashboard = strpos($current_page, '/dashboard') !== false || $current_page === '/' || $current_page === '';
$is_mi_cuenta = strpos($current_page, '/mi-cuenta') !== false;
?>

<!-- Footer de Navegación -->
<footer class="nav-footer">
    <div class="nav-footer-container">
        <button class="nav-item <?= $is_dashboard ? 'active' : '' ?>" data-nav="inicio">
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
        
        <button class="nav-item <?= $is_mi_cuenta ? 'active' : '' ?>" data-nav="cuenta">
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
                    <img src="/assets/images/flujo-resq.png" alt="Control de Flujo" />
                </div>
                <div class="modal-form-content">
                    <h4>Control de Flujo</h4>
                    <p>Registra el control de acceso y flujo de personas</p>
                </div>
            </a>
            
            <a href="/formulario/incidencias" class="modal-form-option">
                <div class="modal-form-icon">
                    <img src="/assets/images/incidencias-resq.png" alt="Incidencias" />
                </div>
                <div class="modal-form-content">
                    <h4>Incidencias</h4>
                    <p>Reporta incidencias y situaciones de atención</p>
                </div>
            </a>
            
            <a href="/formulario/botiquin" class="modal-form-option">
                <div class="modal-form-icon">
                    <img src="/assets/images/botiquin-resq.png" alt="Botiquín" />
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