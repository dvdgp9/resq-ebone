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
                <button class="tab-button active" onclick="showSection('inventario')" id="tab-inventario">
                    📦 Gestión de Inventario
                </button>
                <button class="tab-button" onclick="showSection('solicitudes')" id="tab-solicitudes">
                    📋 Solicitudes
                </button>
            </div>
            
                        <!-- Sección Inventario (con estadísticas integradas) -->
            <div id="section-inventario" class="admin-section active">
                <!-- Estadísticas integradas -->
                <div class="inventario-stats">
                    <div class="stats-inline">
                        🏢 <span id="total-instalaciones">-</span> instalaciones • 
                        📦 <span id="total-elementos">-</span> elementos • 
                        📋 <span id="solicitudes-pendientes">-</span> solicitudes
                    </div>
                </div>
                
                <!-- Filtros Mejorados -->
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <select id="filtro-instalacion" class="admin-filter-select">
                            <option value="">Todas las instalaciones</option>
                        </select>
                    </div>

                    <div class="admin-search-box">
                        <input type="text" id="busqueda-elemento" placeholder="Buscar elementos del inventario...">
                        <span class="admin-search-icon">🔍</span>
                    </div>
                </div>
                
                <!-- Tabla de Inventario -->
                <div class="admin-table-container">
                    <div id="inventario-loading" class="admin-loading" style="display: none;">
                        🔄 Cargando inventario...
                    </div>
                    
                    <div id="inventario-content">
                        <!-- El contenido se genera dinámicamente -->
                    </div>
                </div>
            </div>
            
            <!-- Sección Solicitudes (Solo lectura) -->
            <div id="section-solicitudes" class="admin-section">
                <!-- Filtros Solicitudes Mejorados -->
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <select id="filtro-solicitud-instalacion" class="admin-filter-select">
                            <option value="">Todas las instalaciones</option>
                        </select>
                    </div>
                </div>
                
                <!-- Tabla de Solicitudes (Solo Lectura) -->
                <div class="admin-table-container">
                    <div id="solicitudes-loading" class="admin-loading" style="display: none;">
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
                <input type="hidden" id="elemento-instalacion">
                
                <!-- Mostrar instalación seleccionada -->
                <div class="form-group">
                    <div class="instalacion-context">
                        🏢 <strong>Instalación:</strong> <span id="instalacion-seleccionada">-</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="elemento-nombre">📦 Nombre del elemento *</label>
                    <input type="text" id="elemento-nombre" class="form-input" required 
                           placeholder="Ej: Aspirinas 500mg, Vendas elásticas...">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="elemento-cantidad">🔢 Cantidad actual *</label>
                        <input type="number" id="elemento-cantidad" class="form-input" required min="0" step="1">
                    </div>
                    <div class="form-group">
                        <label for="elemento-unidad">📏 Unidad de medida *</label>
                        <input type="text" id="elemento-unidad" class="form-input" required 
                               placeholder="unidades, cajas, ml, etc.">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="elemento-observaciones">💭 Observaciones</label>
                    <textarea id="elemento-observaciones" class="form-input" rows="3" 
                              placeholder="Información adicional sobre el elemento..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="admin-btn-enhanced btn-secondary" onclick="cerrarModal('modal-elemento')">
                        ✖️ Cancelar
                    </button>
                    <button type="submit" class="admin-btn-enhanced btn-primary">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>



    <script>
        // Variables globales
        let currentSection = 'inventario';
        let instalacionesData = [];

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            loadInstalaciones();
            loadInventario();
            loadEstadisticas();
            configurarEventos();
        });

        // Configurar eventos
        function configurarEventos() {
            // Formularios
            document.getElementById('form-elemento').addEventListener('submit', guardarElemento);

            // Filtrado automático al cambiar instalación (inventario)
            document.getElementById('filtro-instalacion').addEventListener('change', function() {
                loadInventario();
                loadEstadisticas();
            });

            // Filtrado automático al cambiar instalación (solicitudes)
            document.getElementById('filtro-solicitud-instalacion').addEventListener('change', function() {
                loadSolicitudes();
            });

            // Filtrado automático al cambiar búsqueda (con pequeño delay)
            let searchTimeout;
            document.getElementById('busqueda-elemento').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadInventario();
                }, 300); // 300ms de delay para evitar demasiadas llamadas
            });

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
                case 'inventario':
                    loadInventario();
                    loadEstadisticas();
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
                
                // Llenar selects (solo filtros)
                const selects = ['filtro-instalacion', 'filtro-solicitud-instalacion'];
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

        // Cargar estadísticas integradas
        async function loadEstadisticas() {
            try {
                const data = await apiCall('/admin/api/botiquin?action=dashboard');
                
                // Actualizar estadísticas integradas
                document.getElementById('total-instalaciones').textContent = data.stats.total_instalaciones;
                document.getElementById('total-elementos').textContent = data.stats.total_elementos;
                document.getElementById('solicitudes-pendientes').textContent = data.stats.solicitudes_pendientes;
                
            } catch (error) {
                // En caso de error, mostrar guiones
                document.getElementById('total-instalaciones').textContent = '-';
                document.getElementById('total-elementos').textContent = '-';
                document.getElementById('solicitudes-pendientes').textContent = '-';
            }
        }

        // Cargar inventario
        async function loadInventario() {
            document.getElementById('inventario-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'inventario',
                instalacion_id: document.getElementById('filtro-instalacion').value,
                busqueda: document.getElementById('busqueda-elemento').value
            });
            
            try {
                const data = await apiCall('/admin/api/botiquin?' + params);
                const container = document.getElementById('inventario-content');
                container.innerHTML = '';
                
                // Obtener instalaciones a mostrar
                const instalacionesAMostrar = getInstalacionesAMostrar();
                
                if (instalacionesAMostrar.length === 0) {
                    container.innerHTML = `
                        <div class="admin-empty-state">
                            <div class="empty-icon">🏢</div>
                            <p>No se encontraron instalaciones</p>
                            <small>Ajusta los filtros para ver más resultados</small>
                        </div>
                    `;
                } else {
                    // Crear tabla para cada instalación (incluso si no tiene elementos)
                    instalacionesAMostrar.forEach(instalacion => {
                        const elementos = data.inventario[instalacion.nombre] || [];
                        const instalacionDiv = document.createElement('div');
                        instalacionDiv.className = 'instalacion-inventario';
                        
                        // Header con botón de añadir
                        const headerHTML = `
                            <div class="instalacion-header">
                                <h3>🏢 ${instalacion.nombre}</h3>
                                <button class="admin-btn-enhanced btn-primary btn-small" onclick="openCreateElementModal(${instalacion.id}, '${instalacion.nombre}')">
                                    ➕ Añadir Elemento
                                </button>
                            </div>
                        `;
                        
                        // Tabla con elementos o mensaje vacío
                        const tablaHTML = elementos.length > 0 ? `
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Elemento</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${elementos.map(elemento => `
                                        <tr>
                                            <td>
                                                <strong>${elemento.nombre_elemento}</strong>
                                                ${elemento.observaciones ? '<br><small>' + elemento.observaciones + '</small>' : ''}
                                            </td>
                                            <td>
                                                <span class="cantidad">
                                                    ${elemento.cantidad_actual}
                                                </span>
                                            </td>
                                            <td>${elemento.unidad_medida}</td>
                                            <td>
                                                ${formatFecha(elemento.fecha_ultima_actualizacion)}
                                                ${elemento.ultima_actualizacion_por ? '<br><small>por ' + elemento.ultima_actualizacion_por + '</small>' : ''}
                                            </td>
                                            <td>
                                                <button class="admin-btn-enhanced btn-secondary btn-small" 
                                                        onclick="editarElemento(${elemento.id})">
                                                    ✏️ Editar
                                                </button>
                                                <button class="admin-btn-enhanced btn-danger btn-small" 
                                                        onclick="eliminarElemento(${elemento.id})">
                                                    🗑️ Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : `
                            <div class="admin-empty-state">
                                <div class="empty-icon">📦</div>
                                <p>No hay elementos registrados</p>
                                <small>Añade elementos usando el botón superior</small>
                            </div>
                        `;
                        
                        instalacionDiv.innerHTML = headerHTML + tablaHTML;
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
        
        // Función auxiliar para obtener instalaciones a mostrar
        function getInstalacionesAMostrar() {
            const filtroInstalacion = document.getElementById('filtro-instalacion').value;
            
            if (filtroInstalacion) {
                // Si hay filtro, mostrar solo esa instalación
                return instalacionesData.filter(inst => inst.id == filtroInstalacion);
            } else {
                // Si no hay filtro, mostrar todas las instalaciones disponibles
                return instalacionesData;
            }
        }

        // Cargar solicitudes (solo lectura)
        async function loadSolicitudes() {
            document.getElementById('solicitudes-loading').style.display = 'block';
            
            const params = new URLSearchParams({
                action: 'solicitudes',
                instalacion_id: document.getElementById('filtro-solicitud-instalacion').value
            });
            
            try {
                const data = await apiCall('/admin/api/botiquin?' + params);
                
                const container = document.getElementById('solicitudes-content');
                
                if (data.solicitudes.length === 0) {
                    container.innerHTML = `
                        <div class="admin-empty-state">
                            <div class="empty-icon">📭</div>
                            <p>No se encontraron solicitudes</p>
                            <small>Las solicitudes aparecerán aquí cuando los socorristas las envíen</small>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Instalación</th>
                                    <th>Socorrista</th>
                                    <th>Elementos Solicitados</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.solicitudes.map(solicitud => `
                                    <tr>
                                        <td>${formatFecha(solicitud.fecha_solicitud)}</td>
                                        <td>${solicitud.instalacion_nombre}</td>
                                        <td>${solicitud.socorrista_nombre}</td>
                                        <td>
                                            ${solicitud.elementos_solicitados.map(elemento => `
                                                <div style="margin-bottom: 0.25rem;">
                                                    <strong>${elemento.nombre}</strong> × ${elemento.cantidad}
                                                    ${elemento.observaciones ? '<br><small style="color: #666;">' + elemento.observaciones + '</small>' : ''}
                                                </div>
                                            `).join('')}
                                        </td>
                                        <td style="max-width: 200px;">
                                            ${solicitud.mensaje_adicional ? '<small>' + solicitud.mensaje_adicional + '</small>' : '<em style="color: #999;">Sin mensaje</em>'}
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
        function openCreateElementModal(instalacionId = null, instalacionNombre = null) {
            document.getElementById('modal-elemento-title').textContent = '📦 Nuevo Elemento';
            document.getElementById('elemento-id').value = '';
            document.getElementById('form-elemento').reset();
            
            // Configurar contexto de instalación
            if (instalacionId && instalacionNombre) {
                document.getElementById('elemento-instalacion').value = instalacionId;
                document.getElementById('instalacion-seleccionada').textContent = instalacionNombre;
            } else {
                document.getElementById('elemento-instalacion').value = '';
                document.getElementById('instalacion-seleccionada').textContent = '-';
            }
            
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
                categoria: 'general', // Categoría fija por defecto
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
                    loadEstadisticas();
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
                    // Buscar nombre de la instalación
                    const instalacion = instalacionesData.find(inst => inst.id == elemento.instalacion_id);
                    const instalacionNombre = instalacion ? instalacion.nombre : 'Instalación no encontrada';
                    
                    document.getElementById('modal-elemento-title').textContent = '✏️ Editar Elemento';
                    document.getElementById('elemento-id').value = elemento.id;
                    document.getElementById('elemento-instalacion').value = elemento.instalacion_id;
                    document.getElementById('instalacion-seleccionada').textContent = instalacionNombre;
                    document.getElementById('elemento-nombre').value = elemento.nombre_elemento;
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
                    loadEstadisticas();
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