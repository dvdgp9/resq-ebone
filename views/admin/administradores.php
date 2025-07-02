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

// Solo superadmin puede ver esta página
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
    <title>Gestionar Administradores - Admin ResQ</title>
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
                <span class="admin-badge">Super Admin</span>
                <a href="/admin/logout" class="btn btn-secondary btn-small">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container admin-container">
        <!-- Breadcrumb y Título -->
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">🏠 Dashboard</a>
            <span>></span>
            <span>🔐 Administradores</span>
        </div>
        
        <div class="admin-page-header">
            <h1>🔐 Gestionar Administradores</h1>
            <p>Administra los usuarios administradores y coordinadores del sistema</p>
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Nuevo Administrador
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Administradores -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2>📋 Lista de Administradores</h2>
                <div class="table-actions">
                    <button class="btn btn-secondary btn-small" onclick="loadAdministradores()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                🔄 Cargando administradores...
            </div>
            
            <div id="administradores-table" class="admin-table" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Coordinadores</th>
                            <th>Última Conexión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="administradores-tbody">
                        <!-- Datos se cargan via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-data" class="no-data" style="display: none;">
                <div class="no-data-icon">🔐</div>
                <h3>No hay administradores registrados</h3>
                <p>Comienza creando el primer administrador del sistema</p>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    ➕ Crear Primer Administrador
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Administrador -->
    <div id="administrador-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">➕ Nuevo Administrador</h2>
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
                           placeholder="admin@ebone.es">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Mínimo 8 caracteres, mayúscula + minúscula">
                    <small class="form-help">Dejar vacío para mantener password actual (solo edición)</small>
                </div>
                
                <div class="form-group">
                    <label for="tipo">Tipo de Administrador *</label>
                    <select id="tipo" name="tipo" class="form-input" required onchange="toggleCoordinadoresSection()">
                        <option value="">Selecciona tipo</option>
                        <option value="coordinador">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                
                <div class="form-group" id="coordinadores-section" style="display: none;">
                    <label for="coordinadores">Coordinadores Asignados</label>
                    <div id="coordinadores-list" class="checkbox-list">
                        <div class="loading">⏳ Cargando coordinadores...</div>
                    </div>
                    <small class="form-help">Los Super Admin tienen acceso a todos los coordinadores automáticamente</small>
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
                <div class="confirm-icon">🗑️</div>
                <h3 id="confirm-title">¿Confirmar acción?</h3>
                <p id="confirm-message">Esta acción no se puede deshacer</p>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">
                    ❌ Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirm-action">
                    🗑️ Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Variables globales
    let administradores = [];
    let coordinadores = [];
    let editingId = null;
    let currentAction = null;

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        loadAdministradores();
        loadCoordinadores();
    });

    // Cargar administradores
    async function loadAdministradores() {
        showLoading();
        
        try {
            const response = await fetch('/admin/api/administradores');
            const data = await response.json();
            
            if (data.success) {
                administradores = data.administradores;
                renderTable();
            } else {
                showMessage('Error al cargar administradores: ' + data.error, 'error');
            }
            
        } catch (error) {
            console.error('Error loading administradores:', error);
            showMessage('Error al cargar administradores', 'error');
        }
        
        hideLoading();
    }

    // Cargar coordinadores
    async function loadCoordinadores() {
        try {
            const response = await fetch('/admin/api/coordinadores');
            const data = await response.json();
            
            if (data.success) {
                coordinadores = data.coordinadores;
                updateCoordinadoresList();
            } else {
                console.error('Error loading coordinadores:', data.error);
            }
        } catch (error) {
            console.error('Error loading coordinadores:', error);
        }
    }

    // Mostrar/ocultar loading
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('administradores-table').style.display = 'none';
        document.getElementById('no-data').style.display = 'none';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    // Renderizar tabla
    function renderTable() {
        const tbody = document.getElementById('administradores-tbody');
        
        if (administradores.length === 0) {
            document.getElementById('no-data').style.display = 'block';
            return;
        }
        
        document.getElementById('administradores-table').style.display = 'block';
        
        tbody.innerHTML = administradores.map(admin => `
            <tr>
                <td>${admin.id}</td>
                <td>${admin.nombre}</td>
                <td>${admin.email}</td>
                <td>
                    <span class="badge badge-${admin.tipo === 'superadmin' ? 'primary' : 'secondary'}">
                        ${admin.tipo === 'superadmin' ? '🔐 Super Admin' : '👤 Admin'}
                    </span>
                </td>
                <td>
                    <span class="badge badge-${admin.activo ? 'success' : 'danger'}">
                        ${admin.activo ? '✅ Activo' : '❌ Inactivo'}
                    </span>
                </td>
                <td>${admin.total_coordinadores || 0} coordinadores</td>
                <td>${admin.ultima_conexion || 'Nunca'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-small btn-secondary" onclick="editAdministrador(${admin.id})" title="Editar">
                            ✏️
                        </button>
                        ${admin.activo ? `
                        <button class="btn btn-small btn-danger" onclick="confirmDeactivate(${admin.id}, '${admin.nombre}')" title="Desactivar">
                            🗑️
                        </button>
                        ` : `
                        <button class="btn btn-small btn-success" onclick="confirmActivate(${admin.id}, '${admin.nombre}')" title="Activar">
                            ✅
                        </button>
                        `}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Abrir modal crear
    function openCreateModal() {
        editingId = null;
        document.getElementById('modal-title').textContent = '➕ Nuevo Administrador';
        document.getElementById('administrador-form').reset();
        document.getElementById('administrador-id').value = '';
        document.getElementById('password').required = true;
        document.getElementById('save-text').textContent = '💾 Crear';
        
        // Limpiar coordinadores
        document.querySelectorAll('#coordinadores-list input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Limpiar mensajes del modal
        document.getElementById('modal-message-container').innerHTML = '';
        
        toggleCoordinadoresSection();
        document.getElementById('administrador-modal').style.display = 'block';
    }

    // Editar administrador
    function editAdministrador(id) {
        const admin = administradores.find(a => a.id === id);
        if (!admin) return;
        
        editingId = id;
        document.getElementById('modal-title').textContent = '✏️ Editar Administrador';
        document.getElementById('administrador-id').value = admin.id;
        document.getElementById('nombre').value = admin.nombre;
        document.getElementById('email').value = admin.email;
        document.getElementById('tipo').value = admin.tipo;
        document.getElementById('password').required = false;
        document.getElementById('save-text').textContent = '💾 Actualizar';
        
        // Limpiar mensajes del modal
        document.getElementById('modal-message-container').innerHTML = '';
        
        toggleCoordinadoresSection();
        document.getElementById('administrador-modal').style.display = 'block';
    }

    // Toggle sección coordinadores
    function toggleCoordinadoresSection() {
        const tipo = document.getElementById('tipo').value;
        const section = document.getElementById('coordinadores-section');
        
        if (tipo === 'coordinador') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    }

    // Actualizar lista coordinadores
    function updateCoordinadoresList() {
        const container = document.getElementById('coordinadores-list');
        
        if (coordinadores.length === 0) {
            container.innerHTML = '<div class="empty">No hay coordinadores disponibles</div>';
            return;
        }
        
        container.innerHTML = coordinadores.map(coord => `
            <label class="checkbox-item">
                <input type="checkbox" name="coordinadores[]" value="${coord.id}">
                <span class="checkmark"></span>
                ${coord.nombre} <small>(${coord.email})</small>
            </label>
        `).join('');
    }

    // Confirmar desactivar
    function confirmDeactivate(id, nombre) {
        currentAction = { type: 'deactivate', id: id };
        document.getElementById('confirm-title').textContent = 'Desactivar Administrador';
        document.getElementById('confirm-message').textContent = `¿Desactivar al administrador "${nombre}"?`;
        document.getElementById('confirm-action').textContent = '🗑️ Desactivar';
        document.getElementById('confirm-action').className = 'btn btn-danger';
        document.getElementById('confirm-modal').style.display = 'block';
    }

    // Confirmar activar
    function confirmActivate(id, nombre) {
        currentAction = { type: 'activate', id: id };
        document.getElementById('confirm-title').textContent = 'Activar Administrador';
        document.getElementById('confirm-message').textContent = `¿Activar al administrador "${nombre}"?`;
        document.getElementById('confirm-action').textContent = '✅ Activar';
        document.getElementById('confirm-action').className = 'btn btn-success';
        document.getElementById('confirm-modal').style.display = 'block';
    }

    // Cerrar modales
    function closeModal() {
        document.getElementById('administrador-modal').style.display = 'none';
        document.getElementById('modal-message-container').innerHTML = ''; // Limpiar mensajes del modal
        editingId = null;
    }

    function closeConfirmModal() {
        document.getElementById('confirm-modal').style.display = 'none';
        currentAction = null;
    }

    // Submit form
    document.getElementById('administrador-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Obtener coordinadores seleccionados
        const coordinadoresSeleccionados = Array.from(
            document.querySelectorAll('#coordinadores-list input[type="checkbox"]:checked')
        ).map(cb => parseInt(cb.value));
        
        data.coordinadores = coordinadoresSeleccionados;
        
        const saveButton = document.getElementById('save-text');
        const originalText = saveButton.textContent;
        saveButton.textContent = '⏳ Guardando...';
        
        try {
            const response = await fetch('/admin/api/administradores', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showMessage(result.message, 'success');
                closeModal();
                loadAdministradores();
            } else {
                showModalMessage(result.error, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showModalMessage('Error al guardar administrador', 'error');
        } finally {
            saveButton.textContent = originalText;
        }
    });

    // Confirm action
    document.getElementById('confirm-action').addEventListener('click', async function() {
        if (!currentAction) return;
        
        const originalText = this.textContent;
        this.textContent = '⏳ Procesando...';
        this.disabled = true;
        
        try {
            let response;
            
            if (currentAction.type === 'deactivate') {
                response = await fetch('/admin/api/administradores', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: currentAction.id })
                });
            } else {
                // Para activar, usamos POST con activo: true
                response = await fetch('/admin/api/administradores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        id: currentAction.id,
                        activo: true 
                    })
                });
            }
            
            const result = await response.json();
            
            if (result.success) {
                showMessage(result.message, 'success');
                closeConfirmModal();
                loadAdministradores();
            } else {
                showMessage(result.error, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error al realizar la acción', 'error');
        } finally {
            this.textContent = originalText;
            this.disabled = false;
        }
    });

    // Mostrar mensajes en la página principal
    function showMessage(message, type) {
        const container = document.getElementById('message-container');
        const className = type === 'error' ? 'alert-danger' : 'alert-success';
        const icon = type === 'error' ? '❌' : '✅';
        
        container.innerHTML = `
            <div class="alert ${className}">
                ${icon} ${message}
            </div>
        `;
        
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    // Mostrar mensajes en el modal
    function showModalMessage(message, type) {
        const container = document.getElementById('modal-message-container');
        const className = type === 'error' ? 'alert-danger' : 'alert-success';
        const icon = type === 'error' ? '❌' : '✅';
        
        container.innerHTML = `
            <div class="alert ${className}">
                ${icon} ${message}
            </div>
        `;
        
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const adminModal = document.getElementById('administrador-modal');
        const confirmModal = document.getElementById('confirm-modal');
        
        if (event.target === adminModal) {
            closeModal();
        } else if (event.target === confirmModal) {
            closeConfirmModal();
        }
    }
    </script>

</body>
</html>