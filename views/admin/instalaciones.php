<?php
// Vista de Gestión de Instalaciones
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
    <title>Gestionar Instalaciones - Admin ResQ</title>
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
            <span>🏢 Instalaciones</span>
        </div>
        
        <div class="admin-page-header">
            <h1>🏢 Gestionar Instalaciones</h1>
            <p>Administra las instalaciones del sistema y sus socorristas asignados</p>
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Nueva Instalación
            </button>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Tabla de Instalaciones -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h2>📋 Lista de Instalaciones</h2>
                <div class="table-actions">
                    <button class="btn btn-secondary btn-small" onclick="loadInstalaciones()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>
            
            <div id="loading" class="loading-spinner">
                🔄 Cargando instalaciones...
            </div>
            
            <div id="instalaciones-table" class="admin-table" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Coordinador</th>
                            <th>Socorristas</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="instalaciones-tbody">
                        <!-- Datos se cargan via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="no-data" class="no-data" style="display: none;">
                <div class="no-data-icon">📭</div>
                <h3>No hay instalaciones registradas</h3>
                <p>Comienza creando la primera instalación del sistema</p>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    ➕ Crear Primera Instalación
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Instalación -->
    <div id="instalacion-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">➕ Nueva Instalación</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="instalacion-form" class="modal-form">
                <input type="hidden" id="instalacion-id" name="id">
                
                <!-- Mensajes dentro del modal -->
                <div id="modal-message-container"></div>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           placeholder="Ej: Piscina Municipal Centro">
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" class="form-input" rows="2"
                              placeholder="Calle Principal 123, Ciudad"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="coordinador_id">Coordinador *</label>
                    <select id="coordinador_id" name="coordinador_id" class="form-input" required>
                        <option value="">Selecciona un coordinador</option>
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
    
    <!-- Modal de Socorristas -->
    <div id="socorristas-modal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="socorristas-modal-title">👥 Socorristas de la Instalación</h2>
                <button class="modal-close" onclick="closeSocorristasModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="socorristas-loading" class="loading-spinner" style="display: none;">
                    🔄 Cargando socorristas...
                </div>
                
                <div id="socorristas-content" style="display: none;">
                    <div class="socorristas-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total socorristas:</span>
                            <span id="total-socorristas" class="summary-value">0</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Activos:</span>
                            <span id="total-activos" class="summary-value">0</span>
                        </div>
                    </div>
                    
                    <div class="socorristas-list">
                        <h3>📋 Lista de Socorristas</h3>
                        <div id="socorristas-table-container">
                            <!-- Tabla se genera dinámicamente -->
                        </div>
                    </div>
                </div>
                
                <div id="socorristas-empty" style="display: none;">
                    <div class="no-data">
                        <div class="no-data-icon">👥</div>
                        <h3>Sin socorristas asignados</h3>
                        <p>Esta instalación no tiene socorristas asignados actualmente</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeSocorristasModal()">
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
        let instalaciones = [];
        let coordinadores = [];
        let editingId = null;
        
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadCoordinadores();
            loadInstalaciones();
        });
        
        // Cargar coordinadores para el select
        async function loadCoordinadores() {
            try {
                const response = await fetch('/admin/api/coordinadores');
                const data = await response.json();
                
                if (data.success) {
                    coordinadores = data.coordinadores;
                    
                    const select = document.getElementById('coordinador_id');
                    // Limpiar opciones existentes excepto la primera
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    coordinadores.forEach(coord => {
                        const option = document.createElement('option');
                        option.value = coord.id;
                        option.textContent = coord.nombre;
                        select.appendChild(option);
                    });
                    
                    return true;
                }
            } catch (error) {
                showMessage('Error cargando coordinadores: ' + error.message, 'error');
                return false;
            }
        }
        
        // Cargar lista de instalaciones
        async function loadInstalaciones() {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('instalaciones-table').style.display = 'none';
                document.getElementById('no-data').style.display = 'none';
                
                const response = await fetch('/admin/api/instalaciones');
                const data = await response.json();
                
                if (data.success) {
                    instalaciones = data.instalaciones;
                    renderInstalaciones();
                } else {
                    showMessage('Error cargando instalaciones: ' + data.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        // Renderizar tabla de instalaciones
        function renderInstalaciones() {
            const tbody = document.getElementById('instalaciones-tbody');
            
            if (instalaciones.length === 0) {
                document.getElementById('no-data').style.display = 'block';
                return;
            }
            
            tbody.innerHTML = instalaciones.map(inst => `
                <tr>
                    <td>${inst.id}</td>
                    <td>
                        <div class="user-cell">
                            <strong>${escapeHtml(inst.nombre)}</strong>
                        </div>
                    </td>
                    <td>${inst.direccion || '-'}</td>
                    <td>${escapeHtml(inst.coordinador_nombre || '-')}</td>
                    <td>
                        <span class="badge badge-interactive ${inst.total_socorristas > 0 ? 'badge-success' : 'badge-gray'}"
                              ${inst.total_socorristas > 0 ? `
                                  onmouseenter="showSocorristasTooltip(event, ${inst.id})"
                                  onmouseleave="hideTooltip()"
                                  onclick="showSocorristasModal(${inst.id}, '${escapeHtml(inst.nombre)}')"
                                  title="Click para ver detalles"
                              ` : ''}>
                            ${inst.total_socorristas} socorrista${inst.total_socorristas !== 1 ? 's' : ''}
                        </span>
                    </td>
                    <td>${formatDate(inst.fecha_creacion)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-small btn-secondary" onclick="editInstalacion(${inst.id})" title="Editar">
                                ✏️
                            </button>
                            ${inst.total_socorristas > 0 ? `
                                <button class="btn btn-small btn-danger btn-disabled" disabled 
                                        onmouseenter="showTooltip(event, 'No se puede eliminar una instalación con socorristas asignados')"
                                        onmouseleave="hideTooltip()">
                                    🗑️
                                </button>
                            ` : `
                                <button class="btn btn-small btn-danger" onclick="confirmDelete(${inst.id}, '${escapeHtml(inst.nombre)}')" title="Eliminar">
                                    🗑️
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('instalaciones-table').style.display = 'block';
        }
        
        // Abrir modal para crear
        function openCreateModal() {
            document.getElementById('modal-title').textContent = '➕ Nueva Instalación';
            document.getElementById('save-text').textContent = '💾 Crear Instalación';
            document.getElementById('instalacion-form').reset();
            document.getElementById('instalacion-id').value = '';
            document.getElementById('modal-message-container').innerHTML = '';
            editingId = null;
            document.getElementById('instalacion-modal').style.display = 'flex';
            document.getElementById('nombre').focus();
        }
        
        // Editar instalación
        function editInstalacion(id) {
            const inst = instalaciones.find(i => i.id == id);
            if (!inst) return;
            
            document.getElementById('modal-title').textContent = '✏️ Editar Instalación';
            document.getElementById('save-text').textContent = '💾 Guardar Cambios';
            document.getElementById('instalacion-id').value = inst.id;
            document.getElementById('nombre').value = inst.nombre;
            document.getElementById('direccion').value = inst.direccion || '';
            document.getElementById('modal-message-container').innerHTML = '';
            
            // Seleccionar coordinador actual en el dropdown
            const coordinadorSelect = document.getElementById('coordinador_id');
            
            // Si los coordinadores no están cargados, cargarlos primero
            if (coordinadores.length === 0) {
                loadCoordinadores().then(() => {
                    setTimeout(() => {
                        coordinadorSelect.value = inst.coordinador_id;
                    }, 50);
                });
            } else {
                // Si ya están cargados, seleccionar inmediatamente
                coordinadorSelect.value = inst.coordinador_id;
            }
            
            editingId = id;
            document.getElementById('instalacion-modal').style.display = 'flex';
            document.getElementById('nombre').focus();
        }
        
        // Guardar instalación
        document.getElementById('instalacion-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                nombre: formData.get('nombre'),
                direccion: formData.get('direccion') || null,
                coordinador_id: formData.get('coordinador_id')
            };
            
            if (editingId) {
                data.id = editingId;
            }
            
            const saveBtn = document.querySelector('#instalacion-form button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            
            try {
                saveBtn.innerHTML = '🔄 Guardando...';
                saveBtn.disabled = true;
                
                const url = '/admin/api/instalaciones';
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
                    loadInstalaciones();
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
                `¿Estás seguro de eliminar la instalación "${nombre}"? Esta acción no se puede deshacer.`;
            
            document.getElementById('confirm-action').onclick = () => deleteInstalacion(id);
            document.getElementById('confirm-modal').style.display = 'flex';
        }
        
        // Eliminar instalación
        async function deleteInstalacion(id) {
            try {
                const response = await fetch('/admin/api/instalaciones', {
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
                    loadInstalaciones();
                } else {
                    showMessage('Error: ' + result.error, 'error');
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'error');
            }
        }
        
        // Cerrar modales
        function closeModal() {
            document.getElementById('instalacion-modal').style.display = 'none';
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
        
        // Cache para socorristas
        let socorristasCache = {};
        
        // Tooltip para socorristas
        async function showSocorristasTooltip(event, instalacionId) {
            try {
                // Usar cache si está disponible
                let socorristas = socorristasCache[instalacionId];
                
                if (!socorristas) {
                    const response = await fetch(`/admin/api/instalacion-socorristas?instalacion_id=${instalacionId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        socorristas = data.socorristas;
                        socorristasCache[instalacionId] = socorristas;
                    } else {
                        return;
                    }
                }
                
                if (socorristas.length === 0) return;
                
                // Crear mensaje del tooltip
                const message = socorristas.slice(0, 3).map(soc => 
                    `👤 ${soc.nombre}${soc.activo ? '' : ' (inactivo)'}`
                ).join('\n') + (socorristas.length > 3 ? `\n... y ${socorristas.length - 3} más` : '');
                
                showTooltip(event, message);
                
            } catch (error) {
                console.error('Error cargando socorristas:', error);
            }
        }
        
        // Modal de socorristas
        async function showSocorristasModal(instalacionId, instalacionNombre) {
            document.getElementById('socorristas-modal-title').textContent = 
                `👥 Socorristas de ${instalacionNombre}`;
            
            document.getElementById('socorristas-modal').style.display = 'flex';
            document.getElementById('socorristas-loading').style.display = 'block';
            document.getElementById('socorristas-content').style.display = 'none';
            document.getElementById('socorristas-empty').style.display = 'none';
            
            try {
                // Usar cache si está disponible
                let socorristas = socorristasCache[instalacionId];
                
                if (!socorristas) {
                    const response = await fetch(`/admin/api/instalacion-socorristas?instalacion_id=${instalacionId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        socorristas = data.socorristas;
                        socorristasCache[instalacionId] = socorristas;
                    } else {
                        throw new Error(data.error);
                    }
                }
                
                renderSocorristasModal(socorristas);
                
            } catch (error) {
                console.error('Error cargando socorristas:', error);
                showMessage('Error cargando socorristas: ' + error.message, 'error');
                closeSocorristasModal();
            } finally {
                document.getElementById('socorristas-loading').style.display = 'none';
            }
        }
        
        // Renderizar contenido del modal
        function renderSocorristasModal(socorristas) {
            if (socorristas.length === 0) {
                document.getElementById('socorristas-empty').style.display = 'block';
                return;
            }
            
            // Calcular totales
            const totalActivos = socorristas.filter(soc => soc.activo == 1).length;
            
            document.getElementById('total-socorristas').textContent = socorristas.length;
            document.getElementById('total-activos').textContent = totalActivos;
            
            // Generar tabla
            const tableHTML = `
                <table class="installations-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${socorristas.map(soc => `
                            <tr>
                                <td>
                                    <strong>${escapeHtml(soc.nombre)}</strong>
                                </td>
                                <td>${soc.dni}</td>
                                <td>${soc.email || '-'}</td>
                                <td>${soc.telefono || '-'}</td>
                                <td>
                                    <span class="badge ${soc.activo == 1 ? 'badge-success' : 'badge-danger'}">
                                        ${soc.activo == 1 ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                                <td>${formatDate(soc.fecha_creacion)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            document.getElementById('socorristas-table-container').innerHTML = tableHTML;
            document.getElementById('socorristas-content').style.display = 'block';
        }
        
        // Cerrar modal de socorristas
        function closeSocorristasModal() {
            document.getElementById('socorristas-modal').style.display = 'none';
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