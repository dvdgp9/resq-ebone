<?php
// Vista de Gestión de Botiquín Administrativo
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
                <span class="admin-badge"><?= $adminAuth->getDescripcionRol() ?></span>
                <a href="/admin/logout" class="btn btn-secondary btn-small">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container admin-container">
        <!-- Breadcrumb y Título -->
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">🏠 Dashboard</a>
            <span>></span>
            <span>🏥 Gestión de Botiquín</span>
        </div>
        
        <div class="admin-page-header">
            <h1>🏥 Gestión de Botiquín</h1>
            <p>Administra inventarios de botiquín y solicitudes de material</p>
        </div>
        
        <!-- Mensajes -->
        <div id="message-container"></div>
        
        <!-- Navegación de Secciones -->
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
            <button class="tab-button" onclick="showSection('importar')" id="tab-importar">
                📄 Importar CSV
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
                    <div class="table-actions">
                        <button class="btn btn-secondary btn-small" onclick="loadDashboard()">
                            🔄 Actualizar
                        </button>
                    </div>
                </div>
                
                <div id="dashboard-loading" class="loading-spinner">
                    🔄 Cargando dashboard...
                </div>
                
                <div id="instalaciones-resumen" class="admin-table" style="display: none;">
                    <table>
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
                <div id="inventario-loading" class="loading-spinner" style="display: none;">
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
            
            <!-- Lista de Solicitudes -->
            <div class="admin-table-container">
                <div id="solicitudes-loading" class="loading-spinner" style="display: none;">
                    🔄 Cargando solicitudes...
                </div>
                
                <div id="solicitudes-content">
                    <!-- El contenido se genera dinámicamente -->
                </div>
            </div>
        </div>
        
        <!-- Sección Importar CSV -->
        <div id="section-importar" class="admin-section">
            <div class="import-container">
                <div class="import-header">
                    <h2>📄 Importar Inventario desde CSV</h2>
                    <p>Sube un archivo CSV para importar o actualizar elementos del botiquín</p>
                </div>
                
                <div class="import-instructions">
                    <h3>📋 Formato del archivo CSV:</h3>
                    <div class="csv-format">
                        <code>nombre_elemento,categoria,cantidad,unidad_medida,observaciones</code>
                    </div>
                    <ul class="instructions-list">
                        <li><strong>nombre_elemento:</strong> Nombre del producto (requerido)</li>
                        <li><strong>categoria:</strong> medicamentos, material_curacion, instrumental, otros</li>
                        <li><strong>cantidad:</strong> Cantidad numérica (requerido)</li>
                        <li><strong>unidad_medida:</strong> unidades, cajas, frascos, etc.</li>
                        <li><strong>observaciones:</strong> Información adicional (opcional)</li>
                    </ul>
                </div>
                
                <form id="import-form" class="import-form">
                    <div class="form-group">
                        <label for="import-instalacion">Instalación de destino *</label>
                        <select id="import-instalacion" name="instalacion_id" class="form-input" required>
                            <option value="">Selecciona una instalación</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="csv-file">Archivo CSV *</label>
                        <input type="file" id="csv-file" name="csv_file" accept=".csv" class="form-input" required>
                        <div class="form-help">Archivos CSV únicamente (máximo 5MB)</div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            📤 Importar Archivo
                        </button>
                    </div>
                </form>
                
                <div id="import-results" class="import-results" style="display: none;">
                    <!-- Resultados de importación -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Elemento -->
    <div id="elemento-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="elemento-modal-title">➕ Nuevo Elemento</h2>
                <button class="modal-close" onclick="closeElementModal()">&times;</button>
            </div>
            
            <form id="elemento-form" class="modal-form">
                <input type="hidden" id="elemento-id" name="id">
                
                <div id="elemento-modal-message"></div>
                
                <div class="form-group">
                    <label for="elemento-instalacion">Instalación *</label>
                    <select id="elemento-instalacion" name="instalacion_id" class="form-input" required>
                        <option value="">Selecciona una instalación</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="elemento-nombre">Nombre del elemento *</label>
                    <input type="text" id="elemento-nombre" name="nombre_elemento" class="form-input" required
                           placeholder="Ej: Aspirinas 500mg">
                </div>
                
                <div class="form-group">
                    <label for="elemento-categoria">Categoría *</label>
                    <select id="elemento-categoria" name="categoria" class="form-input" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="medicamentos">Medicamentos</option>
                        <option value="material_curacion">Material de Curación</option>
                        <option value="instrumental">Instrumental</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="elemento-cantidad">Cantidad actual *</label>
                        <input type="number" id="elemento-cantidad" name="cantidad_actual" class="form-input" 
                               required min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="elemento-unidad">Unidad de medida *</label>
                        <input type="text" id="elemento-unidad" name="unidad_medida" class="form-input" 
                               required placeholder="unidades">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="elemento-observaciones">Observaciones</label>
                    <textarea id="elemento-observaciones" name="observaciones" class="form-input" rows="2"
                              placeholder="Información adicional sobre el elemento"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeElementModal()">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span id="elemento-save-text">💾 Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Ver Solicitud -->
    <div id="solicitud-modal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="solicitud-modal-title">📋 Detalle de Solicitud</h2>
                <button class="modal-close" onclick="closeSolicitudModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="solicitud-content">
                    <!-- Contenido se genera dinámicamente -->
                </div>
                
                <div class="solicitud-actions">
                    <button class="btn btn-warning" onclick="cambiarEstadoSolicitud('enviada')">
                        📤 Marcar como Enviada
                    </button>
                    <button class="btn btn-success" onclick="cambiarEstadoSolicitud('recibida')">
                        ✅ Marcar como Recibida
                    </button>
                    <button class="btn btn-secondary" onclick="cambiarEstadoSolicitud('pendiente')">
                        ⏳ Marcar como Pendiente
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Variables globales
        let currentSection = 'dashboard';
        let instalacionesData = [];
        let currentSolicitudId = null;
        
        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            loadInstalaciones();
            loadDashboard();
            setupEventListeners();
        });
        
        // Configurar eventos
        function setupEventListeners() {
            // Formulario de elemento
            document.getElementById('elemento-form').addEventListener('submit', saveElemento);
            
            // Formulario de importación
            document.getElementById('import-form').addEventListener('submit', importarCSV);
            
            // Filtros de inventario
            document.getElementById('filtro-instalacion').addEventListener('change', loadInventario);
            document.getElementById('filtro-categoria').addEventListener('change', loadInventario);
            document.getElementById('busqueda-elemento').addEventListener('input', debounce(loadInventario, 500));
            
            // Filtros de solicitudes
            document.getElementById('filtro-solicitud-instalacion').addEventListener('change', loadSolicitudes);
            document.getElementById('filtro-estado').addEventListener('change', loadSolicitudes);
        }
        
        // Cambiar sección activa
        function showSection(section) {
            // Ocultar todas las secciones
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(t => t.classList.remove('active'));
            
            // Mostrar sección seleccionada
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
                case 'importar':
                    loadInstalacionesSelect();
                    break;
            }
        }
        
        // Cargar instalaciones
        async function loadInstalaciones() {
            try {
                const response = await fetch('/controllers/admin/botiquin.php?action=instalaciones');
                const data = await response.json();
                
                if (data.success) {
                    instalacionesData = data.instalaciones;
                    populateInstalacionesSelects();
                }
            } catch (error) {
                showMessage('Error cargando instalaciones: ' + error.message, 'error');
            }
        }
        
        // Poblar selects de instalaciones
        function populateInstalacionesSelects() {
            const selects = [
                'filtro-instalacion',
                'filtro-solicitud-instalacion', 
                'import-instalacion',
                'elemento-instalacion'
            ];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
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
                }
            });
        }
        
        // Cargar dashboard
        async function loadDashboard() {
            if (currentSection !== 'dashboard') return;
            
            document.getElementById('dashboard-loading').style.display = 'block';
            document.getElementById('instalaciones-resumen').style.display = 'none';
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?action=dashboard');
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar estadísticas
                    document.getElementById('total-instalaciones').textContent = data.stats.total_instalaciones;
                    document.getElementById('total-elementos').textContent = data.stats.total_elementos;
                    document.getElementById('elementos-bajo-minimos').textContent = data.stats.elementos_bajo_minimos;
                    document.getElementById('solicitudes-pendientes').textContent = data.stats.solicitudes_pendientes;
                    
                    // Poblar tabla de instalaciones
                    const tbody = document.getElementById('instalaciones-tbody');
                    tbody.innerHTML = '';
                    
                    data.instalaciones.forEach(instalacion => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${instalacion.nombre}</strong></td>
                            <td>${instalacion.coordinador_nombre}</td>
                            <td><span class="badge">${instalacion.total_elementos}</span></td>
                            <td><span class="badge ${instalacion.elementos_bajo_minimos > 0 ? 'badge-warning' : ''}">${instalacion.elementos_bajo_minimos}</span></td>
                            <td><span class="badge ${instalacion.solicitudes_pendientes > 0 ? 'badge-alert' : ''}">${instalacion.solicitudes_pendientes}</span></td>
                            <td>
                                <button class="btn btn-secondary btn-small" onclick="verInventarioInstalacion(${instalacion.id})">
                                    👀 Ver Inventario
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('instalaciones-resumen').style.display = 'block';
                }
            } catch (error) {
                showMessage('Error cargando dashboard: ' + error.message, 'error');
            } finally {
                document.getElementById('dashboard-loading').style.display = 'none';
            }
        }
        
        // Cargar inventario
        async function loadInventario() {
            if (currentSection !== 'inventario') return;
            
            document.getElementById('inventario-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'inventario',
                instalacion_id: document.getElementById('filtro-instalacion').value,
                categoria: document.getElementById('filtro-categoria').value,
                busqueda: document.getElementById('busqueda-elemento').value
            });
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?' + params);
                const data = await response.json();
                
                if (data.success) {
                    renderInventario(data.inventario);
                }
            } catch (error) {
                showMessage('Error cargando inventario: ' + error.message, 'error');
            } finally {
                document.getElementById('inventario-loading').style.display = 'none';
            }
        }
        
        // Renderizar inventario
        function renderInventario(inventario) {
            const container = document.getElementById('inventario-content');
            container.innerHTML = '';
            
            if (Object.keys(inventario).length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <div class="no-data-icon">📦</div>
                        <h3>No se encontraron elementos</h3>
                        <p>No hay elementos que coincidan con los filtros aplicados</p>
                    </div>
                `;
                return;
            }
            
            // Agrupar por instalación
            Object.keys(inventario).forEach(instalacionNombre => {
                const elementos = inventario[instalacionNombre];
                
                const instalacionDiv = document.createElement('div');
                instalacionDiv.className = 'inventario-instalacion';
                instalacionDiv.innerHTML = `
                    <h3>🏢 ${instalacionNombre}</h3>
                    <div class="inventario-tabla">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Elemento</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                    <th>Estado</th>
                                    <th>Última Actualización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${elementos.map(elemento => `
                                    <tr>
                                        <td><strong>${elemento.nombre_elemento}</strong></td>
                                        <td><span class="badge badge-categoria">${formatCategoria(elemento.categoria)}</span></td>
                                        <td><span class="cantidad ${elemento.cantidad_actual <= 5 ? 'cantidad-baja' : ''}">${elemento.cantidad_actual}</span></td>
                                        <td>${elemento.unidad_medida}</td>
                                        <td><span class="badge ${elemento.cantidad_actual <= 5 ? 'badge-warning' : 'badge-success'}">${elemento.cantidad_actual <= 5 ? 'Bajo mínimos' : 'Normal'}</span></td>
                                        <td>${formatDate(elemento.fecha_ultima_actualizacion)}<br><small>${elemento.ultima_actualizacion_por || 'Sistema'}</small></td>
                                        <td>
                                            <button class="btn btn-secondary btn-small" onclick="editElemento(${elemento.id})">
                                                ✏️ Editar
                                            </button>
                                            <button class="btn btn-danger btn-small" onclick="deleteElemento(${elemento.id}, '${elemento.nombre_elemento}')">
                                                🗑️ Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                
                container.appendChild(instalacionDiv);
            });
        }
        
        // Cargar solicitudes
        async function loadSolicitudes() {
            if (currentSection !== 'solicitudes') return;
            
            document.getElementById('solicitudes-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'solicitudes',
                instalacion_id: document.getElementById('filtro-solicitud-instalacion').value,
                estado: document.getElementById('filtro-estado').value
            });
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?' + params);
                const data = await response.json();
                
                if (data.success) {
                    renderSolicitudes(data.solicitudes);
                }
            } catch (error) {
                showMessage('Error cargando solicitudes: ' + error.message, 'error');
            } finally {
                document.getElementById('solicitudes-loading').style.display = 'none';
            }
        }
        
        // Renderizar solicitudes
        function renderSolicitudes(solicitudes) {
            const container = document.getElementById('solicitudes-content');
            container.innerHTML = '';
            
            if (solicitudes.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <div class="no-data-icon">📋</div>
                        <h3>No se encontraron solicitudes</h3>
                        <p>No hay solicitudes que coincidan con los filtros aplicados</p>
                    </div>
                `;
                return;
            }
            
            const table = document.createElement('table');
            table.className = 'admin-table';
            table.innerHTML = `
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
                    ${solicitudes.map(solicitud => `
                        <tr>
                            <td>${formatDate(solicitud.fecha_solicitud)}</td>
                            <td>${solicitud.instalacion_nombre}</td>
                            <td>${solicitud.socorrista_nombre}</td>
                            <td>${solicitud.elementos_solicitados.length} elemento${solicitud.elementos_solicitados.length !== 1 ? 's' : ''}</td>
                            <td><span class="badge badge-${solicitud.estado}">${formatEstado(solicitud.estado)}</span></td>
                            <td>
                                <button class="btn btn-secondary btn-small" onclick="verSolicitud(${solicitud.id})">
                                    👀 Ver Detalle
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            `;
            
            container.appendChild(table);
        }
        
        // Abrir modal crear elemento
        function openCreateElementModal() {
            document.getElementById('elemento-modal-title').textContent = '➕ Nuevo Elemento';
            document.getElementById('elemento-save-text').textContent = '💾 Crear';
            document.getElementById('elemento-form').reset();
            document.getElementById('elemento-id').value = '';
            document.getElementById('elemento-modal').style.display = 'flex';
        }
        
        // Editar elemento
        async function editElemento(elementoId) {
            try {
                const response = await fetch(`/controllers/admin/botiquin.php?action=elemento&elemento_id=${elementoId}`);
                const data = await response.json();
                
                if (data.success) {
                    const elemento = data.elemento;
                    
                    document.getElementById('elemento-modal-title').textContent = '✏️ Editar Elemento';
                    document.getElementById('elemento-save-text').textContent = '💾 Actualizar';
                    
                    document.getElementById('elemento-id').value = elemento.id;
                    document.getElementById('elemento-instalacion').value = elemento.instalacion_id;
                    document.getElementById('elemento-nombre').value = elemento.nombre_elemento;
                    document.getElementById('elemento-categoria').value = elemento.categoria;
                    document.getElementById('elemento-cantidad').value = elemento.cantidad_actual;
                    document.getElementById('elemento-unidad').value = elemento.unidad_medida;
                    document.getElementById('elemento-observaciones').value = elemento.observaciones || '';
                    
                    document.getElementById('elemento-modal').style.display = 'flex';
                }
            } catch (error) {
                showMessage('Error cargando elemento: ' + error.message, 'error');
            }
        }
        
        // Guardar elemento
        async function saveElemento(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const elementoId = document.getElementById('elemento-id').value;
            
            const data = {
                id: elementoId || undefined,
                instalacion_id: formData.get('instalacion_id'),
                nombre_elemento: formData.get('nombre_elemento'),
                categoria: formData.get('categoria'),
                cantidad_actual: parseInt(formData.get('cantidad_actual')),
                unidad_medida: formData.get('unidad_medida'),
                observaciones: formData.get('observaciones')
            };
            
            const action = elementoId ? 'actualizar_elemento' : 'crear_elemento';
            
            try {
                const response = await fetch(`/controllers/admin/botiquin.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    closeElementModal();
                    if (currentSection === 'inventario') loadInventario();
                    if (currentSection === 'dashboard') loadDashboard();
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Error guardando elemento: ' + error.message, 'error');
            }
        }
        
        // Eliminar elemento
        async function deleteElemento(elementoId, nombre) {
            if (!confirm(`¿Estás seguro de que quieres eliminar "${nombre}"?`)) return;
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?action=eliminar_elemento', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: elementoId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    if (currentSection === 'inventario') loadInventario();
                    if (currentSection === 'dashboard') loadDashboard();
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Error eliminando elemento: ' + error.message, 'error');
            }
        }
        
        // Ver solicitud
        async function verSolicitud(solicitudId) {
            // Implementar modal de solicitud
            currentSolicitudId = solicitudId;
            // TODO: Implementar carga de datos de solicitud
            document.getElementById('solicitud-modal').style.display = 'flex';
        }
        
        // Cambiar estado de solicitud
        async function cambiarEstadoSolicitud(nuevoEstado) {
            if (!currentSolicitudId) return;
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?action=actualizar_solicitud', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        id: currentSolicitudId,
                        estado: nuevoEstado 
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                    closeSolicitudModal();
                    loadSolicitudes();
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Error actualizando solicitud: ' + error.message, 'error');
            }
        }
        
        // Importar CSV
        async function importarCSV(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            if (!formData.get('csv_file') || !formData.get('instalacion_id')) {
                showMessage('Selecciona un archivo CSV y una instalación', 'error');
                return;
            }
            
            try {
                const response = await fetch('/controllers/admin/botiquin.php?action=importar_csv', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(`Importación exitosa: ${result.importados} elementos procesados`, 'success');
                    
                    // Mostrar errores si los hay
                    if (result.errores && result.errores.length > 0) {
                        const erroresDiv = document.getElementById('import-results');
                        erroresDiv.innerHTML = `
                            <h4>⚠️ Errores durante la importación:</h4>
                            <ul>${result.errores.map(error => `<li>${error}</li>`).join('')}</ul>
                        `;
                        erroresDiv.style.display = 'block';
                    }
                    
                    form.reset();
                    if (currentSection === 'inventario') loadInventario();
                    if (currentSection === 'dashboard') loadDashboard();
                } else {
                    showMessage(result.error, 'error');
                }
            } catch (error) {
                showMessage('Error importando CSV: ' + error.message, 'error');
            }
        }
        
        // Ver inventario de instalación específica
        function verInventarioInstalacion(instalacionId) {
            document.getElementById('filtro-instalacion').value = instalacionId;
            showSection('inventario');
        }
        
        // Cargar instalaciones para selects
        function loadInstalacionesSelect() {
            if (instalacionesData.length === 0) {
                loadInstalaciones();
            }
        }
        
        // Cerrar modales
        function closeElementModal() {
            document.getElementById('elemento-modal').style.display = 'none';
            document.getElementById('elemento-modal-message').innerHTML = '';
        }
        
        function closeSolicitudModal() {
            document.getElementById('solicitud-modal').style.display = 'none';
            currentSolicitudId = null;
        }
        
        // Funciones de utilidad
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
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
        }
        
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            messageDiv.innerHTML = `
                ${message}
                <button onclick="this.parentElement.remove()" class="message-close">&times;</button>
            `;
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 5000);
        }
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
    </script>
</body>
</html>