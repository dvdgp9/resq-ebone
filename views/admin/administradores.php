<?php
// Vista de Gestión de Administradores
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

$admin = $adminAuth->getAdminActual();

// Solo superadmins pueden gestionar administradores
if ($admin['tipo'] !== 'superadmin') {
    header('Location: /admin/dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Administradores/as - Admin ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="dashboard-page">
    <header class="header admin-header">
        <div class="header-content">
            <div class="logo">
                <img src="/assets/images/logo.png" alt="ResQ Logo" class="header-logo">
            </div>
            <div class="header-title">
                <h1>👤 Gestionar Administradores/as</h1>
            </div>
            <div class="header-actions">
                <span class="admin-badge">Super Admin</span>
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
        <!-- Breadcrumb y Título -->
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">🏠 Dashboard</a>
            <span>></span>
            <span>👤 Administradores/as</span>
        </div>
        
        <div class="admin-page-header">
            <p>Administra todos los usuarios del sistema: superadmins, admins y coordinadores/as</p>
            <button class="btn-tag btn-tag-primary" onclick="openCreateModal()">
                ➕ Nuevo/a Administrador/a
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Administradores -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2>📋 Lista de Administradores/as</h2>
                <div class="table-actions">
                    <button class="btn-tag btn-tag-secondary" onclick="loadAdministradores()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                <div class="loading-spinner-content">
                    <div class="loading-spinner-icon"></div>
                    <div class="loading-spinner-text">Cargando administradores...</div>
                </div>
            </div>
            
            <div id="administradores-table" class="admin-table" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="administradores-tbody">
                        <!-- Datos se cargan via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-data" class="no-data" style="display: none;">
                <div class="no-data-icon">👤</div>
                <h3>No hay administradores/as registrados/as</h3>
                <p>Comienza creando el primer/a administrador/a del sistema</p>
                <button class="btn-tag btn-tag-primary" onclick="openCreateModal()">
                    ➕ Crear Primer/a Administrador/a
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Administrador -->
    <div id="administrador-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">➕ Nuevo/a Administrador/a</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="administrador-form" class="modal-form">
                <input type="hidden" id="administrador-id" name="id">
                
                <!-- Mensajes dentro del modal -->
                <div id="modal-message-container"></div>
                
                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           placeholder="Ej: Juan Pérez García">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-input" required
                           placeholder="juan.perez@ebone.es">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-input"
                           placeholder="666 123 456">
                </div>
                
                <div class="form-group">
                    <label for="tipo">Tipo de Usuario *</label>
                    <select id="tipo" name="tipo" class="form-input" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="superadmin">🔑 Superadmin</option>
                        <option value="admin">👨‍💼 Admin</option>
                        <option value="coordinador">👥 Coordinador</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Contraseña segura">
                    <small class="form-help" id="password-help">
                        Mínimo 8 caracteres. Deja vacío para mantener la actual (solo edición).
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="activo">Estado</label>
                    <select id="activo" name="activo" class="form-input">
                        <option value="1">✅ Activo</option>
                        <option value="0">❌ Inactivo</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-tag btn-tag-secondary" onclick="closeModal()">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn-tag btn-tag-primary">
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
                <p id="confirm-message">¿Estás seguro de que deseas realizar esta acción?</p>
            </div>
            
                            <div class="modal-actions">
                    <button type="button" class="btn-tag btn-tag-secondary" onclick="closeConfirmModal()">
                        ❌ Cancelar
                    </button>
                    <button type="button" class="btn-tag btn-tag-danger" id="confirm-button" onclick="confirmAction()">
                        ✅ Confirmar
                    </button>
                </div>
        </div>
    </div>

    <script>
        let administradores = [];
        let editingAdministrador = null;
        let pendingAction = null;

        // Cargar administradores al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadAdministradores();
        });

        // Cargar lista de administradores
        async function loadAdministradores() {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('administradores-table').style.display = 'none';
                document.getElementById('no-data').style.display = 'none';

                const response = await fetch('/admin/api/administradores');
                const data = await response.json();

                if (data.success) {
                    administradores = data.administradores;
                    displayAdministradores();
                } else {
                    showMessage('error', data.error || 'Error al cargar administradores/as');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('error', 'Error de conexión al cargar administradores/as');
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        // Mostrar administradores en la tabla
        function displayAdministradores() {
            const tbody = document.getElementById('administradores-tbody');
            
            if (administradores.length === 0) {
                document.getElementById('no-data').style.display = 'block';
                document.getElementById('administradores-table').style.display = 'none';
                return;
            }

            tbody.innerHTML = '';
            
            administradores.forEach(admin => {
                const row = document.createElement('tr');
                
                // Iconos por tipo
                const tipoIcons = {
                    'superadmin': '🔑',
                    'admin': '👨‍💼',
                    'coordinador': '👥'
                };
                
                // Estado
                const estadoBadge = admin.activo == 1 
                    ? '<span class="badge badge-success">✅ Activo</span>'
                    : '<span class="badge badge-danger">❌ Inactivo</span>';
                
                // Fecha formateada
                const fecha = new Date(admin.fecha_creacion).toLocaleDateString('es-ES');
                
                row.innerHTML = `
                    <td>${admin.id}</td>
                    <td>
                        <span class="tipo-badge tipo-${admin.tipo}">
                            ${tipoIcons[admin.tipo]} ${admin.tipo.charAt(0).toUpperCase() + admin.tipo.slice(1)}
                        </span>
                    </td>
                    <td>${escapeHtml(admin.nombre)}</td>
                    <td>${escapeHtml(admin.email)}</td>
                    <td>${admin.telefono ? escapeHtml(admin.telefono) : '<span class="text-muted">-</span>'}</td>
                    <td>${estadoBadge}</td>
                    <td>${fecha}</td>
                    <td class="actions">
                        <button class="btn-tag btn-tag-secondary" onclick="editAdministrador(${admin.id})" title="Editar">
                            ✏️ Editar
                        </button>
                        ${admin.id != <?= $admin['id'] ?> ? `
                        <button class="btn-tag btn-tag-danger" onclick="deleteAdministrador(${admin.id})" title="Desactivar">
                            🗑️ Eliminar
                        </button>
                        ` : '<span class="text-muted">-</span>'}
                    </td>
                `;
                
                tbody.appendChild(row);
            });

            document.getElementById('administradores-table').style.display = 'block';
            document.getElementById('no-data').style.display = 'none';
        }

        // Abrir modal para crear administrador
        function openCreateModal() {
            editingAdministrador = null;
            document.getElementById('modal-title').textContent = '➕ Nuevo/a Administrador/a';
            document.getElementById('administrador-form').reset();
            document.getElementById('administrador-id').value = '';
            document.getElementById('save-text').textContent = '💾 Guardar';
            document.getElementById('password-help').textContent = 'Mínimo 8 caracteres.';
            document.getElementById('password').required = true;
            document.getElementById('modal-message-container').innerHTML = '';
            document.getElementById('administrador-modal').classList.add('show');
        }

        // Editar administrador
        function editAdministrador(id) {
            const admin = administradores.find(a => a.id == id);
            if (!admin) return;

            editingAdministrador = admin;
            document.getElementById('modal-title').textContent = '✏️ Editar Administrador/a';
            document.getElementById('administrador-id').value = admin.id;
            document.getElementById('nombre').value = admin.nombre;
            document.getElementById('email').value = admin.email;
            document.getElementById('telefono').value = admin.telefono || '';
            document.getElementById('tipo').value = admin.tipo;
            document.getElementById('activo').value = admin.activo;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('password-help').textContent = 'Deja vacío para mantener la contraseña actual.';
            document.getElementById('save-text').textContent = '💾 Actualizar';
            document.getElementById('modal-message-container').innerHTML = '';
            document.getElementById('administrador-modal').classList.add('show');
        }

        // Eliminar (desactivar) administrador
        function deleteAdministrador(id) {
            const admin = administradores.find(a => a.id == id);
            if (!admin) return;

            pendingAction = {
                type: 'delete',
                id: id,
                data: admin
            };

            document.getElementById('confirm-message').textContent = 
                `¿Estás seguro de que deseas desactivar al/a la administrador/a "${admin.nombre}"?`;
            document.getElementById('confirm-modal').classList.add('show');
        }

        // Manejar envío del formulario
        document.getElementById('administrador-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Validaciones
            if (!data.password && !editingAdministrador) {
                showModalMessage('error', 'La contraseña es obligatoria para nuevos/as administradores/as');
                return;
            }
            
            if (data.password && data.password.length < 8) {
                showModalMessage('error', 'La contraseña debe tener al menos 8 caracteres');
                return;
            }

            try {
                const isEditing = editingAdministrador !== null;
                const url = '/admin/api/administradores';
                const method = isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('success', result.message);
                    closeModal();
                    loadAdministradores();
                } else {
                    showModalMessage('error', result.error || 'Error al guardar administrador/a');
                }
            } catch (error) {
                console.error('Error:', error);
                showModalMessage('error', 'Error de conexión al guardar administrador/a');
            }
        });

        // Confirmar acción
        async function confirmAction() {
            if (!pendingAction) return;

            try {
                if (pendingAction.type === 'delete') {
                    const response = await fetch('/admin/api/administradores', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: pendingAction.id })
                    });

                    const result = await response.json();

                    if (result.success) {
                        showMessage('success', result.message);
                        loadAdministradores();
                    } else {
                        showMessage('error', result.error || 'Error al desactivar administrador');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('error', 'Error de conexión');
            } finally {
                closeConfirmModal();
            }
        }

        // Cerrar modal
        function closeModal() {
            document.getElementById('administrador-modal').classList.remove('show');
            editingAdministrador = null;
        }

        // Cerrar modal de confirmación
        function closeConfirmModal() {
            document.getElementById('confirm-modal').classList.remove('show');
            pendingAction = null;
        }

        // Mostrar mensaje
        function showMessage(type, message) {
            const container = document.getElementById('message-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? '✅' : '❌';
            
            container.innerHTML = `
                <div class="alert ${alertClass}">
                    ${icon} ${message}
                </div>
            `;
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Mostrar mensaje en modal
        function showModalMessage(type, message) {
            const container = document.getElementById('modal-message-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? '✅' : '❌';
            
            container.innerHTML = `
                <div class="alert ${alertClass}">
                    ${icon} ${message}
                </div>
            `;
        }

        // Escapar HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modal1 = document.getElementById('administrador-modal');
            const modal2 = document.getElementById('confirm-modal');
            
            if (event.target == modal1) {
                closeModal();
            }
            if (event.target == modal2) {
                closeConfirmModal();
            }
        }
    </script>
</body>
</html>