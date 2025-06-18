<?php
// Vista de Gestión de Socorristas
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();

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
        <!-- Breadcrumb y Título -->
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">🏠 Dashboard</a>
            <span>></span>
            <span>👥 Socorristas</span>
        </div>
        
        <div class="admin-page-header">
            <h1>👥 Gestionar Socorristas</h1>
            <p>Administra los socorristas del sistema y sus datos personales</p>
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Nuevo Socorrista
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Socorristas -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2>📋 Lista de Socorristas</h2>
                <div class="table-actions">
                    <button class="btn btn-secondary btn-small" onclick="loadSocorristas()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                🔄 Cargando socorristas...
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
                <div class="no-data-icon">📭</div>
                <h3>No hay socorristas registrados</h3>
                <p>Comienza creando el primer socorrista del sistema</p>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    ➕ Crear Primer Socorrista
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Socorrista -->
    <div id="socorrista-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">➕ Nuevo Socorrista</h2>
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
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span id="save-text">💾 Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de Confirmación -->
    <div id="confirm-modal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2>⚠️ Confirmar Acción</h2>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <p id="confirm-message">¿Estás seguro de realizar esta acción?</p>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">
                    ❌ Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirm-action">
                    ✅ Confirmar
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
                            <button class="btn btn-small btn-secondary" onclick="editSocorrista(${soc.id})" title="Editar">
                                ✏️
                            </button>
                            ${soc.activo == 1 ? `
                                <button class="btn btn-small btn-danger" onclick="confirmDeactivate(${soc.id}, '${escapeHtml(soc.nombre)}')" title="Desactivar">
                                    🗑️
                                </button>
                            ` : `
                                <button class="btn btn-small btn-danger btn-disabled" disabled title="Ya está inactivo">
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
            document.getElementById('modal-title').textContent = '➕ Nuevo Socorrista';
            document.getElementById('save-text').textContent = '💾 Crear Socorrista';
            document.getElementById('socorrista-form').reset();
            document.getElementById('socorrista-id').value = '';
            document.getElementById('modal-message-container').innerHTML = '';
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
            document.getElementById('email').value = soc.email || '';
            document.getElementById('telefono').value = soc.telefono || '';
            document.getElementById('modal-message-container').innerHTML = '';
            
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
                email: formData.get('email') || null,
                telefono: formData.get('telefono') || null,
                instalacion_id: formData.get('instalacion_id')
            };
            
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