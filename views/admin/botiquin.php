<?php
require_once __DIR__ . '/../../classes/AdminAuthService.php';

$adminAuth = new AdminAuthService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

$admin = $adminAuth->getAdminActual();
$permissions = $adminAuth->getPermissionsService();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Botiquín - Admin ResQ</title>
    <link rel="stylesheet" href="<?= assetVersion('/assets/css/styles.css') ?>">
</head>
<body>
    <div class="container">
        <main class="admin-botiquin-container">
            <!-- Header Admin -->
            <header class="admin-header">
                <div class="admin-header-content">
                    <div class="admin-nav">
                        <a href="/admin/dashboard" class="btn btn-secondary btn-small">← Dashboard</a>
                        <h1>🏥 Gestión de Botiquín</h1>
                    </div>
                    <div class="admin-user-info">
                        <span>👤 <?= htmlspecialchars($admin['nombre']) ?></span>
                        <span class="admin-badge"><?= $adminAuth->getDescripcionRol() ?></span>
                        <a href="/admin/logout" class="btn btn-secondary btn-small">Cerrar Sesión</a>
                    </div>
                </div>
            </header>

            <!-- Mensajes -->
            <div id="message-container"></div>
            
            <!-- Navegación de pestañas -->
            <div class="admin-tabs">
                <button class="tab-button active" onclick="showSection('dashboard')" id="tab-dashboard">
                    📊 Dashboard
                </button>
                <button class="tab-button" onclick="showSection('inventario')" id="tab-inventario">
                    📦 Inventario
                </button>
                <button class="tab-button" onclick="showSection('solicitudes')" id="tab-solicitudes">
                    📋 Solicitudes
                </button>
            </div>
            
            <!-- Sección Dashboard -->
            <div id="section-dashboard" class="admin-section active">
                <div class="admin-stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-content">
                            <div class="stat-value" id="total-instalaciones">-</div>
                            <div class="stat-label">Instalaciones</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-content">
                            <div class="stat-value" id="total-elementos">-</div>
                            <div class="stat-label">Elementos</div>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-content">
                            <div class="stat-value" id="elementos-bajo-minimos">-</div>
                            <div class="stat-label">Bajo Mínimos</div>
                        </div>
                    </div>
                    <div class="stat-card alert">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <div class="stat-value" id="solicitudes-pendientes">-</div>
                            <div class="stat-label">Solicitudes Pendientes</div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen por Instalación -->
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2>🏢 Resumen por Instalación</h2>
                        <button class="btn btn-secondary btn-small" onclick="loadDashboard()">
                            🔄 Actualizar
                        </button>
                    </div>
                    
                    <div id="dashboard-loading" class="loading">
                        🔄 Cargando dashboard...
                    </div>
                    
                    <div id="instalaciones-resumen" style="display: none;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Instalación</th>
                                    <th>Coordinador</th>
                                    <th>Total Elementos</th>
                                    <th>Bajo Mínimos</th>
                                    <th>Solicitudes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="instalaciones-tbody">
                                <!-- Datos se cargan via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Sección Inventario -->
            <div id="section-inventario" class="admin-section">
                <!-- Filtros -->
                <div class="admin-filters">
                    <div class="filter-group">
                        <label for="filtro-instalacion">Instalación:</label>
                        <select id="filtro-instalacion" class="form-input">
                            <option value="">Todas las instalaciones</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro-categoria">Categoría:</label>
                        <select id="filtro-categoria" class="form-input">
                            <option value="todos">Todas las categorías</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="material_curacion">Material de Curación</option>
                            <option value="instrumental">Instrumental</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="busqueda-elemento">Buscar:</label>
                        <input type="text" id="busqueda-elemento" class="form-input" placeholder="Nombre del elemento...">
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" onclick="openCreateElementModal()">
                            ➕ Nuevo Elemento
                        </button>
                        <button class="btn btn-secondary" onclick="loadInventario()">
                            🔍 Buscar
                        </button>
                    </div>
                </div>
                
                <!-- Tabla de Inventario -->
                <div class="admin-table-container">
                    <div id="inventario-loading" class="loading" style="display: none;">
                        🔄 Cargando inventario...
                    </div>
                    
                    <div id="inventario-content">
                        <!-- El contenido se genera dinámicamente -->
                    </div>
                </div>
            </div>
            
            <!-- Sección Solicitudes -->
            <div id="section-solicitudes" class="admin-section">
                <!-- Filtros Solicitudes -->
                <div class="admin-filters">
                    <div class="filter-group">
                        <label for="filtro-solicitud-instalacion">Instalación:</label>
                        <select id="filtro-solicitud-instalacion" class="form-input">
                            <option value="">Todas las instalaciones</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filtro-estado">Estado:</label>
                        <select id="filtro-estado" class="form-input">
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="enviada">Enviadas</option>
                            <option value="recibida">Recibidas</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-secondary" onclick="loadSolicitudes()">
                            🔍 Buscar
                        </button>
                    </div>
                </div>
                
                <!-- Tabla de Solicitudes -->
                <div class="admin-table-container">
                    <div id="solicitudes-loading" class="loading" style="display: none;">
                        🔄 Cargando solicitudes...
                    </div>
                    
                    <div id="solicitudes-content">
                        <!-- El contenido se genera dinámicamente -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para crear/editar elemento -->
    <div id="modal-elemento" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modal-elemento-title">📦 Nuevo Elemento</h2>
                <button class="modal-close" onclick="cerrarModal('modal-elemento')">&times;</button>
            </div>
            
            <form id="form-elemento">
                <input type="hidden" id="elemento-id">
                
                <div class="form-group">
                    <label for="elemento-instalacion">🏢 Instalación *</label>
                    <select id="elemento-instalacion" class="form-input" required>
                        <option value="">Seleccionar instalación</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="elemento-nombre">📦 Nombre del elemento *</label>
                    <input type="text" id="elemento-nombre" class="form-input" required 
                           placeholder="Ej: Aspirinas 500mg, Vendas elásticas...">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="elemento-categoria">📋 Categoría *</label>
                        <select id="elemento-categoria" class="form-input" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="medicamentos">Medicamentos</option>
                            <option value="material_curacion">Material de Curación</option>
                            <option value="instrumental">Instrumental</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="elemento-unidad">📏 Unidad de medida *</label>
                        <input type="text" id="elemento-unidad" class="form-input" required 
                               placeholder="unidades, cajas, ml, etc.">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="elemento-cantidad">🔢 Cantidad actual *</label>
                    <input type="number" id="elemento-cantidad" class="form-input" required min="0" step="1">
                </div>
                
                <div class="form-group">
                    <label for="elemento-observaciones">💭 Observaciones</label>
                    <textarea id="elemento-observaciones" class="form-input" rows="3" 
                              placeholder="Información adicional sobre el elemento..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-elemento')">
                        ✖️ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para gestionar solicitud -->
    <div id="modal-solicitud" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📋 Gestionar Solicitud</h2>
                <button class="modal-close" onclick="cerrarModal('modal-solicitud')">&times;</button>
            </div>
            
            <div id="solicitud-details">
                <!-- Los detalles se cargan dinámicamente -->
            </div>
            
            <form id="form-solicitud">
                <input type="hidden" id="solicitud-id">
                
                <div class="form-group">
                    <label for="solicitud-estado">🏷️ Estado *</label>
                    <select id="solicitud-estado" class="form-input" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="enviada">Enviada</option>
                        <option value="recibida">Recibida</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="solicitud-observaciones">💭 Observaciones de coordinación</label>
                    <textarea id="solicitud-observaciones" class="form-input" rows="3" 
                              placeholder="Información adicional de la coordinación..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-solicitud')">
                        ✖️ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variables globales
        let currentSection = 'dashboard';
        let instalacionesData = [];
        let dashboardData = null;

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            loadInstalaciones();
            loadDashboard();
            configurarEventos();
        });

        // Configurar eventos
        function configurarEventos() {
            // Formularios
            document.getElementById('form-elemento').addEventListener('submit', guardarElemento);
            document.getElementById('form-solicitud').addEventListener('submit', actualizarSolicitud);

            // Cerrar modales al hacer clic fuera
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        cerrarModal(this.id);
                    }
                });
            });
        }

        // Funciónes de navegación
        function showSection(section) {
            // Ocultar todas las secciones
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(t => t.classList.remove('active'));
            
            // Mostrar sección actual
            document.getElementById('section-' + section).classList.add('active');
            document.getElementById('tab-' + section).classList.add('active');
            
            currentSection = section;
            
            // Cargar datos según la sección
            switch(section) {
                case 'dashboard':
                    loadDashboard();
                    break;
                case 'inventario':
                    loadInventario();
                    break;
                case 'solicitudes':
                    loadSolicitudes();
                    break;
            }
        }

        // API calls
        async function apiCall(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Error de servidor');
                }
                
                return data;
            } catch (error) {
                console.error('Error en API:', error);
                mostrarError(error.message);
                throw error;
            }
        }

        // Cargar instalaciones
        async function loadInstalaciones() {
            try {
                const data = await apiCall('/admin/api/botiquin?action=instalaciones');
                instalacionesData = data.instalaciones;
                
                // Llenar selects
                const selects = ['filtro-instalacion', 'filtro-solicitud-instalacion', 'elemento-instalacion'];
                selects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    const currentValue = select.value;
                    
                    // Limpiar opciones existentes (excepto la primera)
                    while (select.children.length > 1) {
                        select.removeChild(select.lastChild);
                    }
                    
                    // Añadir instalaciones
                    instalacionesData.forEach(instalacion => {
                        const option = document.createElement('option');
                        option.value = instalacion.id;
                        option.textContent = instalacion.nombre;
                        select.appendChild(option);
                    });
                    
                    // Restaurar valor si existe
                    if (currentValue) {
                        select.value = currentValue;
                    }
                });
            } catch (error) {
                console.error('Error cargando instalaciones:', error);
            }
        }

        // Cargar dashboard
        async function loadDashboard() {
            document.getElementById('dashboard-loading').style.display = 'block';
            document.getElementById('instalaciones-resumen').style.display = 'none';
            
            try {
                const data = await apiCall('/admin/api/botiquin?action=dashboard');
                dashboardData = data;
                
                // Actualizar estadísticas
                document.getElementById('total-instalaciones').textContent = data.stats.total_instalaciones;
                document.getElementById('total-elementos').textContent = data.stats.total_elementos;
                document.getElementById('elementos-bajo-minimos').textContent = data.stats.elementos_bajo_minimos;
                document.getElementById('solicitudes-pendientes').textContent = data.stats.solicitudes_pendientes;
                
                // Actualizar tabla de instalaciones
                const tbody = document.getElementById('instalaciones-tbody');
                tbody.innerHTML = '';
                
                data.instalaciones.forEach(instalacion => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${instalacion.nombre}</td>
                        <td>${instalacion.coordinador_nombre}</td>
                        <td>${instalacion.total_elementos}</td>
                        <td class="${instalacion.elementos_bajo_minimos > 0 ? 'warning' : ''}">
                            ${instalacion.elementos_bajo_minimos}
                        </td>
                        <td class="${instalacion.solicitudes_pendientes > 0 ? 'alert' : ''}">
                            ${instalacion.solicitudes_pendientes}
                        </td>
                        <td>
                            <button class="btn btn-small btn-secondary" 
                                    onclick="verInstalacion(${instalacion.id})">
                                👁️ Ver
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                
                document.getElementById('dashboard-loading').style.display = 'none';
                document.getElementById('instalaciones-resumen').style.display = 'block';
                
            } catch (error) {
                document.getElementById('dashboard-loading').innerHTML = 
                    '<div class="error">❌ Error cargando dashboard</div>';
            }
        }

        // Cargar inventario
        async function loadInventario() {
            document.getElementById('inventario-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'inventario',
                instalacion_id: document.getElementById('filtro-instalacion').value,
                categoria: document.getElementById('filtro-categoria').value,
                busqueda: document.getElementById('busqueda-elemento').value
            });
            
            try {
                const data = await apiCall('/admin/api/botiquin?' + params);
                
                const container = document.getElementById('inventario-content');
                container.innerHTML = '';
                
                if (Object.keys(data.inventario).length === 0) {
                    container.innerHTML = '<div class="no-results">📭 No se encontraron elementos</div>';
                } else {
                    // Crear tabla para cada instalación
                    Object.entries(data.inventario).forEach(([instalacionNombre, elementos]) => {
                        const instalacionDiv = document.createElement('div');
                        instalacionDiv.className = 'instalacion-inventario';
                        instalacionDiv.innerHTML = `
                            <h3>🏢 ${instalacionNombre}</h3>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Elemento</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${elementos.map(elemento => `
                                        <tr class="${elemento.cantidad_actual <= 5 ? 'warning' : ''}">
                                            <td>
                                                <strong>${elemento.nombre_elemento}</strong>
                                                ${elemento.observaciones ? '<br><small>' + elemento.observaciones + '</small>' : ''}
                                            </td>
                                            <td>${formatCategoria(elemento.categoria)}</td>
                                            <td>
                                                <span class="cantidad ${elemento.cantidad_actual <= 5 ? 'bajo-minimos' : ''}">
                                                    ${elemento.cantidad_actual}
                                                </span>
                                            </td>
                                            <td>${elemento.unidad_medida}</td>
                                            <td>
                                                ${formatFecha(elemento.fecha_ultima_actualizacion)}
                                                ${elemento.ultima_actualizacion_por ? '<br><small>por ' + elemento.ultima_actualizacion_por + '</small>' : ''}
                                            </td>
                                            <td>
                                                <button class="btn btn-small btn-secondary" 
                                                        onclick="editarElemento(${elemento.id})">
                                                    ✏️ Editar
                                                </button>
                                                <button class="btn btn-small btn-danger" 
                                                        onclick="eliminarElemento(${elemento.id})">
                                                    🗑️ Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                        container.appendChild(instalacionDiv);
                    });
                }
                
                document.getElementById('inventario-loading').style.display = 'none';
                
            } catch (error) {
                document.getElementById('inventario-content').innerHTML = 
                    '<div class="error">❌ Error cargando inventario</div>';
                document.getElementById('inventario-loading').style.display = 'none';
            }
        }

        // Cargar solicitudes
        async function loadSolicitudes() {
            document.getElementById('solicitudes-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'solicitudes',
                instalacion_id: document.getElementById('filtro-solicitud-instalacion').value,
                estado: document.getElementById('filtro-estado').value
            });
            
            try {
                const data = await apiCall('/admin/api/botiquin?' + params);
                
                const container = document.getElementById('solicitudes-content');
                
                if (data.solicitudes.length === 0) {
                    container.innerHTML = '<div class="no-results">📭 No se encontraron solicitudes</div>';
                } else {
                    container.innerHTML = `
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Instalación</th>
                                    <th>Socorrista</th>
                                    <th>Elementos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.solicitudes.map(solicitud => `
                                    <tr>
                                        <td>${formatFecha(solicitud.fecha_solicitud)}</td>
                                        <td>${solicitud.instalacion_nombre}</td>
                                        <td>${solicitud.socorrista_nombre}</td>
                                        <td>
                                            <small>${solicitud.elementos_solicitados.length} elemento(s)</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-${solicitud.estado}">
                                                ${formatEstado(solicitud.estado)}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-small btn-primary" 
                                                    onclick="gestionarSolicitud(${solicitud.id})">
                                                📋 Gestionar
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                }
                
                document.getElementById('solicitudes-loading').style.display = 'none';
                
            } catch (error) {
                document.getElementById('solicitudes-content').innerHTML = 
                    '<div class="error">❌ Error cargando solicitudes</div>';
                document.getElementById('solicitudes-loading').style.display = 'none';
            }
        }

        // Funciones de modal
        function openCreateElementModal() {
            document.getElementById('modal-elemento-title').textContent = '📦 Nuevo Elemento';
            document.getElementById('elemento-id').value = '';
            document.getElementById('form-elemento').reset();
            document.getElementById('modal-elemento').style.display = 'flex';
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Funciones CRUD
        async function guardarElemento(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('elemento-id').value,
                instalacion_id: document.getElementById('elemento-instalacion').value,
                nombre_elemento: document.getElementById('elemento-nombre').value,
                categoria: document.getElementById('elemento-categoria').value,
                unidad_medida: document.getElementById('elemento-unidad').value,
                cantidad_actual: parseInt(document.getElementById('elemento-cantidad').value),
                observaciones: document.getElementById('elemento-observaciones').value
            };
            
            try {
                const action = formData.id ? 'actualizar_elemento' : 'crear_elemento';
                const data = await apiCall('/admin/api/botiquin?action=' + action, {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                mostrarExito(data.message);
                cerrarModal('modal-elemento');
                
                if (currentSection === 'inventario') {
                    loadInventario();
                }
                if (currentSection === 'dashboard') {
                    loadDashboard();
                }
                
            } catch (error) {
                // Error ya mostrado en apiCall
            }
        }

        async function editarElemento(id) {
            // Buscar elemento en los datos cargados
            // Para simplificar, recargar desde servidor
            try {
                const data = await apiCall('/admin/api/botiquin?action=inventario');
                let elemento = null;
                
                Object.values(data.inventario).forEach(elementos => {
                    const found = elementos.find(e => e.id == id);
                    if (found) elemento = found;
                });
                
                if (elemento) {
                    document.getElementById('modal-elemento-title').textContent = '✏️ Editar Elemento';
                    document.getElementById('elemento-id').value = elemento.id;
                    document.getElementById('elemento-instalacion').value = elemento.instalacion_id;
                    document.getElementById('elemento-nombre').value = elemento.nombre_elemento;
                    document.getElementById('elemento-categoria').value = elemento.categoria;
                    document.getElementById('elemento-unidad').value = elemento.unidad_medida;
                    document.getElementById('elemento-cantidad').value = elemento.cantidad_actual;
                    document.getElementById('elemento-observaciones').value = elemento.observaciones || '';
                    
                    document.getElementById('modal-elemento').style.display = 'flex';
                }
                
            } catch (error) {
                mostrarError('Error cargando elemento para editar');
            }
        }

        async function eliminarElemento(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                return;
            }
            
            try {
                const data = await apiCall('/admin/api/botiquin?action=eliminar_elemento', {
                    method: 'POST',
                    body: JSON.stringify({ id: id })
                });
                
                mostrarExito(data.message);
                
                if (currentSection === 'inventario') {
                    loadInventario();
                }
                if (currentSection === 'dashboard') {
                    loadDashboard();
                }
                
            } catch (error) {
                // Error ya mostrado en apiCall
            }
        }

        async function gestionarSolicitud(id) {
            try {
                // Obtener detalles de la solicitud
                const data = await apiCall('/admin/api/botiquin?action=solicitudes');
                const solicitud = data.solicitudes.find(s => s.id == id);
                
                if (solicitud) {
                    // Mostrar detalles de la solicitud
                    const detailsContainer = document.getElementById('solicitud-details');
                    detailsContainer.innerHTML = `
                        <div class="solicitud-info">
                            <h3>📋 Detalles de la Solicitud</h3>
                            <div class="info-grid">
                                <div><strong>Fecha:</strong> ${formatFecha(solicitud.fecha_solicitud)}</div>
                                <div><strong>Instalación:</strong> ${solicitud.instalacion_nombre}</div>
                                <div><strong>Socorrista:</strong> ${solicitud.socorrista_nombre}</div>
                                <div><strong>Estado actual:</strong> <span class="badge badge-${solicitud.estado}">${formatEstado(solicitud.estado)}</span></div>
                            </div>
                            
                            <h4>📦 Elementos Solicitados:</h4>
                            <div class="elementos-solicitados">
                                ${solicitud.elementos_solicitados.map(elemento => `
                                    <div class="elemento-solicitado">
                                        <strong>${elemento.nombre}</strong> - 
                                        Cantidad: ${elemento.cantidad} 
                                        ${elemento.observaciones ? '<br><small>' + elemento.observaciones + '</small>' : ''}
                                    </div>
                                `).join('')}
                            </div>
                            
                            ${solicitud.mensaje_adicional ? `
                                <h4>💬 Mensaje del Socorrista:</h4>
                                <div class="mensaje-adicional">${solicitud.mensaje_adicional}</div>
                            ` : ''}
                            
                            ${solicitud.observaciones_coordinacion ? `
                                <h4>📝 Observaciones de Coordinación:</h4>
                                <div class="observaciones-coordinacion">${solicitud.observaciones_coordinacion}</div>
                            ` : ''}
                        </div>
                    `;
                    
                    // Llenar formulario
                    document.getElementById('solicitud-id').value = solicitud.id;
                    document.getElementById('solicitud-estado').value = solicitud.estado;
                    document.getElementById('solicitud-observaciones').value = solicitud.observaciones_coordinacion || '';
                    
                    document.getElementById('modal-solicitud').style.display = 'flex';
                }
                
            } catch (error) {
                mostrarError('Error cargando detalles de la solicitud');
            }
        }

        async function actualizarSolicitud(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('solicitud-id').value,
                estado: document.getElementById('solicitud-estado').value,
                observaciones_coordinacion: document.getElementById('solicitud-observaciones').value
            };
            
            try {
                const data = await apiCall('/admin/api/botiquin?action=actualizar_solicitud', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
                
                mostrarExito(data.message);
                cerrarModal('modal-solicitud');
                
                if (currentSection === 'solicitudes') {
                    loadSolicitudes();
                }
                if (currentSection === 'dashboard') {
                    loadDashboard();
                }
                
            } catch (error) {
                // Error ya mostrado en apiCall
            }
        }

        // Funciones de utilidad
        function verInstalacion(instalacionId) {
            // Cambiar a pestaña inventario y filtrar por instalación
            document.getElementById('filtro-instalacion').value = instalacionId;
            showSection('inventario');
        }

        function formatCategoria(categoria) {
            const categorias = {
                'medicamentos': 'Medicamentos',
                'material_curacion': 'Material de Curación',
                'instrumental': 'Instrumental',
                'otros': 'Otros'
            };
            return categorias[categoria] || categoria;
        }

        function formatEstado(estado) {
            const estados = {
                'pendiente': 'Pendiente',
                'enviada': 'Enviada',
                'recibida': 'Recibida'
            };
            return estados[estado] || estado;
        }

        function formatFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Funciones de mensaje
        function mostrarExito(mensaje) {
            mostrarMensaje(mensaje, 'success');
        }

        function mostrarError(mensaje) {
            mostrarMensaje(mensaje, 'error');
        }

        function mostrarMensaje(mensaje, tipo) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${tipo}`;
            messageDiv.innerHTML = `
                <span>${tipo === 'success' ? '✅' : '❌'} ${mensaje}</span>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;
            
            container.appendChild(messageDiv);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>