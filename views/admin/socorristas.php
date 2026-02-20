<?php
// Vista de Gestión de Socorristas
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();

// Intentar restaurar sesión desde cookie si no está autenticado
if (!$adminAuth->estaAutenticadoAdmin()) {
    $adminAuth->restaurarSesionDesdeCookie();
}

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

$admin = $adminAuth->getAdminActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Socorristas - Admin ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="dashboard-page">
    <?php 
    $pageTitle = "Socorristas";
    include __DIR__ . '/../partials/header-admin.php'; 
    ?>
    
    <div class="container admin-container">
        <!-- Breadcrumb y Título -->
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Dashboard</a>
            <span>></span>
            <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Socorristas</span>
        </div>
        
        <div class="admin-page-header">
            <h1><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Gestionar Socorristas</h1>
            <p>Administra los socorristas del sistema y sus datos personales</p>
            <button class="btn-tag btn-tag-primary" onclick="openCreateModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Nuevo Socorrista
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Socorristas -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg> Lista de Socorristas</h2>
                <div class="table-actions">
                    <button class="btn-tag btn-tag-secondary" onclick="loadSocorristas()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg> Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                <div class="loading-spinner-content">
                    <div class="loading-spinner-icon"></div>
                    <div class="loading-spinner-text">Cargando socorristas...</div>
                </div>
            </div>
            
            <div id="socorristas-table" class="admin-table" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DNI</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Instalación</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="socorristas-tbody">
                        <!-- Datos se cargan via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-data" class="no-data" style="display: none;">
                <div class="no-data-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></div>
                <h3>No hay socorristas registrados</h3>
                <p>Comienza creando el primer socorrista del sistema</p>
                <button class="btn-tag btn-tag-primary" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Crear Primer Socorrista
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Socorrista -->
    <div id="socorrista-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Nuevo Socorrista</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="socorrista-form" class="modal-form">
                <input type="hidden" id="socorrista-id" name="id">
                
                <!-- Mensajes dentro del modal -->
                <div id="modal-message-container"></div>
                
                <div class="form-group">
                    <label for="dni">DNI *</label>
                    <input type="text" id="dni" name="dni" class="form-input" required
                           placeholder="12345678A" maxlength="9">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           placeholder="Juan Pérez García">
                </div>
                
                <div class="form-group">
                    <label for="username">Usuario *</label>
                    <input type="text" id="username" name="username" class="form-input" required
                           placeholder="juan.perez" maxlength="50">
                    <small class="form-help">Usuario para iniciar sesión (sin espacios)</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <span id="password-required">*</span></label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Mínimo 6 caracteres" minlength="6">
                    <small class="form-help" id="password-help">Contraseña para iniciar sesión</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="juan.perez@ebone.es">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-input"
                           placeholder="666 123 456">
                </div>
                
                <div class="form-group">
                    <label for="instalacion_id">Instalación *</label>
                    <select id="instalacion_id" name="instalacion_id" class="form-input" required>
                        <option value="">Selecciona una instalación</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-tag btn-tag-secondary" onclick="closeModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> Cancelar
                    </button>
                    <button type="submit" class="btn-tag btn-tag-primary">
                        <span id="save-text"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg> Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de Confirmación -->
    <div id="confirm-modal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px; color: #f57c00;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg> Confirmar Acción</h2>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <p id="confirm-message">¿Estás seguro de realizar esta acción?</p>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-tag btn-tag-secondary" onclick="closeConfirmModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> Cancelar
                </button>
                <button type="button" class="btn-tag btn-tag-primary" id="confirm-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><path d="M20 6 9 17l-5-5"/></svg> Confirmar
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Variables globales
        let socorristas = [];
        let instalaciones = [];
        let editingId = null;
        
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadInstalaciones();
            loadSocorristas();
        });
        
        // Cargar instalaciones para el select
        async function loadInstalaciones() {
            try {
                const response = await fetch('/admin/api/instalaciones');
                const data = await response.json();
                
                if (data.success) {
                    instalaciones = data.instalaciones.filter(inst => inst.activo == 1);
                    
                    const select = document.getElementById('instalacion_id');
                    // Limpiar opciones existentes excepto la primera
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    instalaciones.forEach(inst => {
                        const option = document.createElement('option');
                        option.value = inst.id;
                        option.textContent = `${inst.nombre} (${inst.coordinador_nombre})`;
                        select.appendChild(option);
                    });
                    
                    return true;
                }
            } catch (error) {
                showMessage('Error cargando instalaciones: ' + error.message, 'error');
                return false;
            }
        }
        
        // Cargar lista de socorristas
        async function loadSocorristas() {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('socorristas-table').style.display = 'none';
                document.getElementById('no-data').style.display = 'none';
                
                const response = await fetch('/admin/api/socorristas');
                const data = await response.json();
                
                if (data.success) {
                    socorristas = data.socorristas;
                    renderSocorristas();
                } else {
                    showMessage('Error cargando socorristas: ' + data.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        // Renderizar tabla de socorristas
        function renderSocorristas() {
            const tbody = document.getElementById('socorristas-tbody');
            
            if (socorristas.length === 0) {
                document.getElementById('no-data').style.display = 'block';
                return;
            }
            
            tbody.innerHTML = socorristas.map(soc => `
                <tr>
                    <td>${soc.id}</td>
                    <td>
                        <strong>${escapeHtml(soc.dni)}</strong>
                    </td>
                    <td>
                        <div class="user-cell">
                            <strong>${escapeHtml(soc.nombre)}</strong>
                        </div>
                    </td>
                    <td>
                        ${soc.email ? `<a href="mailto:${soc.email}" class="email-link">${escapeHtml(soc.email)}</a>` : '-'}
                    </td>
                    <td>${soc.telefono || '-'}</td>
                    <td>${escapeHtml(soc.instalacion_nombre || '-')}</td>
                    <td>
                        <span class="badge ${soc.activo == 1 ? 'badge-success' : 'badge-danger'}">
                            ${soc.activo == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>${formatDate(soc.fecha_creacion)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-tag btn-tag-secondary" onclick="editSocorrista(${soc.id})" title="Editar">
                                ✏️
                            </button>
                            ${soc.activo == 1 ? `
                                <button class="btn-tag btn-tag-danger" onclick="confirmDeactivate(${soc.id}, '${escapeHtml(soc.nombre)}')" title="Desactivar">
                                    🗑️
                                </button>
                            ` : `
                                <button class="btn-tag btn-tag-danger btn-disabled" disabled title="Ya está inactivo">
                                    🗑️
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('socorristas-table').style.display = 'block';
        }
        
        // Abrir modal para crear
        function openCreateModal() {
            document.getElementById('modal-title').textContent = '➥ Nuevo Socorrista';
            document.getElementById('save-text').textContent = '💾 Crear Socorrista';
            document.getElementById('socorrista-form').reset();
            document.getElementById('socorrista-id').value = '';
            document.getElementById('modal-message-container').innerHTML = '';
            // Contraseña obligatoria al crear
            document.getElementById('password').required = true;
            document.getElementById('password-required').textContent = '*';
            document.getElementById('password-help').textContent = 'Contraseña para iniciar sesión';
            editingId = null;
            document.getElementById('socorrista-modal').style.display = 'flex';
            document.getElementById('dni').focus();
        }
        
        // Editar socorrista
        function editSocorrista(id) {
            const soc = socorristas.find(s => s.id == id);
            if (!soc) return;
            
            document.getElementById('modal-title').textContent = '✏️ Editar Socorrista';
            document.getElementById('save-text').textContent = '💾 Guardar Cambios';
            document.getElementById('socorrista-id').value = soc.id;
            document.getElementById('dni').value = soc.dni;
            document.getElementById('nombre').value = soc.nombre;
            document.getElementById('username').value = soc.username || '';
            document.getElementById('password').value = '';
            document.getElementById('email').value = soc.email || '';
            document.getElementById('telefono').value = soc.telefono || '';
            document.getElementById('modal-message-container').innerHTML = '';
            // Contraseña opcional al editar
            document.getElementById('password').required = false;
            document.getElementById('password-required').textContent = '';
            document.getElementById('password-help').textContent = 'Dejar vacío para mantener la actual';
            
            // Seleccionar instalación actual en el dropdown
            const instalacionSelect = document.getElementById('instalacion_id');
            
            // Si las instalaciones no están cargadas, cargarlas primero
            if (instalaciones.length === 0) {
                loadInstalaciones().then(() => {
                    setTimeout(() => {
                        instalacionSelect.value = soc.instalacion_id;
                    }, 50);
                });
            } else {
                // Si ya están cargadas, seleccionar inmediatamente
                instalacionSelect.value = soc.instalacion_id;
            }
            
            editingId = id;
            document.getElementById('socorrista-modal').style.display = 'flex';
            document.getElementById('dni').focus();
        }
        
        // Guardar socorrista
        document.getElementById('socorrista-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                dni: formData.get('dni'),
                nombre: formData.get('nombre'),
                username: formData.get('username'),
                email: formData.get('email') || null,
                telefono: formData.get('telefono') || null,
                instalacion_id: formData.get('instalacion_id')
            };
            
            // Solo incluir contraseña si se proporciona
            const password = formData.get('password');
            if (password) {
                data.password = password;
            }
            
            if (editingId) {
                data.id = editingId;
            }
            
            const saveBtn = document.querySelector('#socorrista-form button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            
            try {
                saveBtn.innerHTML = '🔄 Guardando...';
                saveBtn.disabled = true;
                
                const url = '/admin/api/socorristas';
                const method = editingId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    closeModal();
                    loadSocorristas();
                } else {
                    showModalMessage('Error: ' + result.error, 'error');
                }
            } catch (error) {
                showModalMessage('Error de conexión: ' + error.message, 'error');
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        });
        
        // Confirmar desactivación
        function confirmDeactivate(id, nombre) {
            document.getElementById('confirm-message').textContent = 
                `¿Estás seguro de desactivar al socorrista "${nombre}"? Podrá ser reactivado posteriormente.`;
            
            document.getElementById('confirm-action').onclick = () => deactivateSocorrista(id);
            document.getElementById('confirm-modal').style.display = 'flex';
        }
        
        // Desactivar socorrista
        async function deactivateSocorrista(id) {
            try {
                const response = await fetch('/admin/api/socorristas', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    closeConfirmModal();
                    loadSocorristas();
                } else {
                    showMessage('Error: ' + result.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            }
        }
        
        // Cerrar modales
        function closeModal() {
            document.getElementById('socorrista-modal').style.display = 'none';
        }
        
        function closeConfirmModal() {
            document.getElementById('confirm-modal').style.display = 'none';
        }
        
        // Mostrar mensajes
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            messageDiv.textContent = message;
            
            container.innerHTML = '';
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
        // Mostrar mensajes dentro del modal
        function showModalMessage(message, type) {
            const container = document.getElementById('modal-message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            messageDiv.textContent = message;
            
            container.innerHTML = '';
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
        // Utilidades
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES');
        }
        
        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html> 