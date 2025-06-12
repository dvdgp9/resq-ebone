<?php
// Vista de Gestión de Coordinadores
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
    <title>Gestionar Coordinadores - Admin ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="dashboard-page">
    <header class="header admin-header">
        <div class="header-content">
            <div class="logo">
                🛡️ Admin ResQ
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
            <span>👥 Coordinadores</span>
        </div>
        
        <div class="admin-page-header">
            <h1>👥 Gestionar Coordinadores</h1>
            <p>Administra los coordinadores del sistema y sus instalaciones asignadas</p>
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Nuevo Coordinador
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Coordinadores -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2>📋 Lista de Coordinadores</h2>
                <div class="table-actions">
                    <button class="btn btn-secondary btn-small" onclick="loadCoordinadores()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                🔄 Cargando coordinadores...
            </div>
            
            <div id="coordinadores-table" class="admin-table" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Instalaciones</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="coordinadores-tbody">
                        <!-- Datos se cargan via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-data" class="no-data" style="display: none;">
                <div class="no-data-icon">📭</div>
                <h3>No hay coordinadores registrados</h3>
                <p>Comienza creando el primer coordinador del sistema</p>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    ➕ Crear Primer Coordinador
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Coordinador -->
    <div id="coordinador-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">➕ Nuevo Coordinador</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="coordinador-form" class="modal-form">
                <input type="hidden" id="coordinador-id" name="id">
                
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
    
    <!-- Modal de Instalaciones -->
    <div id="instalaciones-modal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="instalaciones-modal-title">🏢 Instalaciones del Coordinador</h2>
                <button class="modal-close" onclick="closeInstallationsModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="instalaciones-loading" class="loading-spinner" style="display: none;">
                    🔄 Cargando instalaciones...
                </div>
                
                <div id="instalaciones-content" style="display: none;">
                    <div class="instalaciones-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total instalaciones:</span>
                            <span id="total-instalaciones" class="summary-value">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total socorristas:</span>
                            <span id="total-socorristas" class="summary-value">0</span>
                        </div>
                    </div>
                    
                    <div class="instalaciones-list">
                        <h3>📋 Lista de Instalaciones</h3>
                        <div id="instalaciones-table-container">
                            <!-- Tabla se genera dinámicamente -->
                        </div>
                    </div>
                </div>
                
                <div id="instalaciones-empty" style="display: none;">
                    <div class="no-data">
                        <div class="no-data-icon">🏢</div>
                        <h3>Sin instalaciones asignadas</h3>
                        <p>Este coordinador no tiene instalaciones asignadas actualmente</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeInstallationsModal()">
                    ❌ Cerrar
                </button>
            </div>
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
        let coordinadores = [];
        let editingId = null;
        
        // Cargar coordinadores al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadCoordinadores();
        });
        
        // Cargar lista de coordinadores
        async function loadCoordinadores() {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('coordinadores-table').style.display = 'none';
                document.getElementById('no-data').style.display = 'none';
                
                const response = await fetch('/admin/api/coordinadores');
                const data = await response.json();
                
                if (data.success) {
                    coordinadores = data.coordinadores;
                    renderCoordinadores();
                } else {
                    showMessage('Error cargando coordinadores: ' + data.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        // Renderizar tabla de coordinadores
        function renderCoordinadores() {
            const tbody = document.getElementById('coordinadores-tbody');
            
            if (coordinadores.length === 0) {
                document.getElementById('no-data').style.display = 'block';
                return;
            }
            
            tbody.innerHTML = coordinadores.map(coord => `
                <tr>
                    <td>${coord.id}</td>
                    <td>
                        <div class="user-cell">
                            <strong>${escapeHtml(coord.nombre)}</strong>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:${coord.email}" class="email-link">
                            ${escapeHtml(coord.email)}
                        </a>
                    </td>
                    <td>${coord.telefono || '-'}</td>
                    <td>
                        <span class="badge badge-interactive ${coord.total_instalaciones > 0 ? 'badge-success' : 'badge-gray'}"
                              ${coord.total_instalaciones > 0 ? `
                                  onmouseenter="showInstallationsTooltip(event, ${coord.id})"
                                  onmouseleave="hideTooltip()"
                                  onclick="showInstallationsModal(${coord.id}, '${escapeHtml(coord.nombre)}')"
                                  title="Click para ver detalles"
                              ` : ''}>
                            ${coord.total_instalaciones} instalación${coord.total_instalaciones !== 1 ? 'es' : ''}
                        </span>
                    </td>
                    <td>${formatDate(coord.fecha_creacion)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-small btn-secondary" onclick="editCoordinador(${coord.id})" title="Editar">
                                ✏️
                            </button>
                            ${coord.total_instalaciones > 0 ? `
                                <button class="btn btn-small btn-danger btn-disabled" disabled 
                                        onmouseenter="showTooltip(event, 'No se puede eliminar un coordinador con instalaciones asignadas')"
                                        onmouseleave="hideTooltip()">
                                    🗑️
                                </button>
                            ` : `
                                <button class="btn btn-small btn-danger" onclick="confirmDelete(${coord.id}, '${escapeHtml(coord.nombre)}')" title="Eliminar">
                                    🗑️
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('coordinadores-table').style.display = 'block';
        }
        
        // Abrir modal para crear
        function openCreateModal() {
            document.getElementById('modal-title').textContent = '➕ Nuevo Coordinador';
            document.getElementById('save-text').textContent = '💾 Crear Coordinador';
            document.getElementById('coordinador-form').reset();
            document.getElementById('coordinador-id').value = '';
            document.getElementById('modal-message-container').innerHTML = '';
            editingId = null;
            document.getElementById('coordinador-modal').style.display = 'flex';
            document.getElementById('nombre').focus();
        }
        
        // Editar coordinador
        function editCoordinador(id) {
            const coord = coordinadores.find(c => c.id == id);
            if (!coord) return;
            
            document.getElementById('modal-title').textContent = '✏️ Editar Coordinador';
            document.getElementById('save-text').textContent = '💾 Guardar Cambios';
            document.getElementById('coordinador-id').value = coord.id;
            document.getElementById('nombre').value = coord.nombre;
            document.getElementById('email').value = coord.email;
            document.getElementById('telefono').value = coord.telefono || '';
            document.getElementById('modal-message-container').innerHTML = '';
            
            editingId = id;
            document.getElementById('coordinador-modal').style.display = 'flex';
            document.getElementById('nombre').focus();
        }
        
        // Guardar coordinador
        document.getElementById('coordinador-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                nombre: formData.get('nombre'),
                email: formData.get('email'),
                telefono: formData.get('telefono') || null
            };
            
            if (editingId) {
                data.id = editingId;
            }
            
            const saveBtn = document.querySelector('#coordinador-form button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            
            try {
                saveBtn.innerHTML = '🔄 Guardando...';
                saveBtn.disabled = true;
                
                const url = '/admin/api/coordinadores';
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
                    loadCoordinadores();
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
        
        // Confirmar eliminación
        function confirmDelete(id, nombre) {
            document.getElementById('confirm-message').textContent = 
                `¿Estás seguro de eliminar al coordinador "${nombre}"? Esta acción no se puede deshacer.`;
            
            document.getElementById('confirm-action').onclick = () => deleteCoordinador(id);
            document.getElementById('confirm-modal').style.display = 'flex';
        }
        
        // Eliminar coordinador
        async function deleteCoordinador(id) {
            try {
                const response = await fetch('/admin/api/coordinadores', {
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
                    loadCoordinadores();
                } else {
                    showMessage('Error: ' + result.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            }
        }
        
        // Cerrar modales
        function closeModal() {
            document.getElementById('coordinador-modal').style.display = 'none';
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
        
        // Tooltip inteligente
        let tooltipElement = null;
        
        function showTooltip(event, message) {
            // Crear tooltip si no existe
            if (!tooltipElement) {
                tooltipElement = document.createElement('div');
                tooltipElement.className = 'custom-tooltip';
                document.body.appendChild(tooltipElement);
            }
            
            tooltipElement.textContent = message;
            tooltipElement.style.display = 'block';
            
            // Posicionar tooltip
            const rect = event.target.getBoundingClientRect();
            const tooltipRect = tooltipElement.getBoundingClientRect();
            
            // Calcular posición óptima
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let top = rect.top - tooltipRect.height - 10;
            
            // Ajustar si se sale por la derecha
            if (left + tooltipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tooltipRect.width - 10;
            }
            
            // Ajustar si se sale por la izquierda
            if (left < 10) {
                left = 10;
            }
            
            // Ajustar si se sale por arriba
            if (top < 10) {
                top = rect.bottom + 10;
            }
            
            tooltipElement.style.left = left + 'px';
            tooltipElement.style.top = top + 'px';
        }
        
        function hideTooltip() {
            if (tooltipElement) {
                tooltipElement.style.display = 'none';
            }
        }
        
        // Cache para instalaciones
        let installationsCache = {};
        
        // Tooltip para instalaciones
        async function showInstallationsTooltip(event, coordinadorId) {
            try {
                // Usar cache si está disponible
                let instalaciones = installationsCache[coordinadorId];
                
                if (!instalaciones) {
                    const response = await fetch(`/admin/api/coordinador-instalaciones?coordinador_id=${coordinadorId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        instalaciones = data.instalaciones;
                        installationsCache[coordinadorId] = instalaciones;
                    } else {
                        return;
                    }
                }
                
                if (instalaciones.length === 0) return;
                
                // Crear mensaje del tooltip
                const message = instalaciones.slice(0, 3).map(inst => 
                    `🏢 ${inst.nombre}${inst.total_socorristas > 0 ? ` (${inst.total_socorristas} socorristas)` : ''}`
                ).join('\n') + (instalaciones.length > 3 ? `\n... y ${instalaciones.length - 3} más` : '');
                
                showTooltip(event, message);
                
            } catch (error) {
                console.error('Error cargando instalaciones:', error);
            }
        }
        
        // Modal de instalaciones
        async function showInstallationsModal(coordinadorId, coordinadorNombre) {
            document.getElementById('instalaciones-modal-title').textContent = 
                `🏢 Instalaciones de ${coordinadorNombre}`;
            
            document.getElementById('instalaciones-modal').style.display = 'flex';
            document.getElementById('instalaciones-loading').style.display = 'block';
            document.getElementById('instalaciones-content').style.display = 'none';
            document.getElementById('instalaciones-empty').style.display = 'none';
            
            try {
                // Usar cache si está disponible
                let instalaciones = installationsCache[coordinadorId];
                
                if (!instalaciones) {
                    const response = await fetch(`/admin/api/coordinador-instalaciones?coordinador_id=${coordinadorId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        instalaciones = data.instalaciones;
                        installationsCache[coordinadorId] = instalaciones;
                    } else {
                        throw new Error(data.error);
                    }
                }
                
                renderInstallationsModal(instalaciones);
                
            } catch (error) {
                console.error('Error cargando instalaciones:', error);
                showMessage('Error cargando instalaciones: ' + error.message, 'error');
                closeInstallationsModal();
            } finally {
                document.getElementById('instalaciones-loading').style.display = 'none';
            }
        }
        
        // Renderizar contenido del modal
        function renderInstallationsModal(instalaciones) {
            if (instalaciones.length === 0) {
                document.getElementById('instalaciones-empty').style.display = 'block';
                return;
            }
            
            // Calcular totales
            const totalSocorristas = instalaciones.reduce((sum, inst) => sum + parseInt(inst.total_socorristas), 0);
            
            document.getElementById('total-instalaciones').textContent = instalaciones.length;
            document.getElementById('total-socorristas').textContent = totalSocorristas;
            
            // Generar tabla
            const tableHTML = `
                <table class="installations-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Socorristas</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${instalaciones.map(inst => `
                            <tr>
                                <td>
                                    <strong>${escapeHtml(inst.nombre)}</strong>
                                </td>
                                <td>${inst.direccion || '-'}</td>
                                <td>
                                    <span class="badge ${inst.total_socorristas > 0 ? 'badge-success' : 'badge-gray'}">
                                        ${inst.total_socorristas} socorrista${inst.total_socorristas !== 1 ? 's' : ''}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge ${inst.activo == 1 ? 'badge-success' : 'badge-danger'}">
                                        ${inst.activo == 1 ? 'Activa' : 'Inactiva'}
                                    </span>
                                </td>
                                <td>${formatDate(inst.fecha_creacion)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('instalaciones-table-container').innerHTML = tableHTML;
            document.getElementById('instalaciones-content').style.display = 'block';
        }
        
        // Cerrar modal de instalaciones
        function closeInstallationsModal() {
            document.getElementById('instalaciones-modal').style.display = 'none';
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