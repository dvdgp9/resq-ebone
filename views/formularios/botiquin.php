<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../classes/AuthService.php';

// Verificar autenticación
$auth = new AuthService();
if (!$auth->estaAutenticado()) {
    header('Location: /login');
    exit;
}

$socorrista = $auth->getSocorristaActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Botiquín - ResQ</title>
    <link rel="stylesheet" href="<?= assetVersion('/assets/css/styles.css') ?>">
</head>
<body>
    <div class="container">
        <main class="botiquin-container" style="padding-bottom: 2rem;">
            <?php 
            $titulo = "Gestión de Botiquín";
            include __DIR__ . '/../partials/header-universal.php'; 
            ?>

            <!-- Controles con acciones integradas -->
            <div class="botiquin-controls">
                <div class="search-box">
                    <input type="text" id="search-input" placeholder="Buscar elementos del botiquín...">
                    <span class="search-icon">🔍</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary btn-action" onclick="mostrarModalSolicitar()" title="Solicitar material">
                        📧 Solicitar
                    </button>
                </div>
            </div>

            <!-- Contenido del inventario -->
            <div id="inventario-container">
                <div class="loading">🔄 Cargando inventario...</div>
            </div>
        </main>
    </div>



    <!-- Modal para solicitar material -->
    <div id="modal-solicitud" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 class="modal-title">📧 Solicitar Material a Coordinación</h2>
                <button class="modal-close" onclick="cerrarModal('modal-solicitud')">&times;</button>
            </div>
            
            <form id="form-solicitud">
                <!-- Sección de elementos -->
                <div class="form-section">
                    <h3>📋 Elementos a Solicitar</h3>
                    <div class="form-help">Añade todos los elementos que necesitas y especifica las cantidades</div>
                    
                    <div id="elementos-solicitud" class="elementos-solicitud-container">
                        <div class="elemento-solicitud-item">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>📦 Nombre del elemento *</label>
                                    <input type="text" placeholder="Ej: Aspirinas 500mg, Vendas elásticas..." required>
                                    <div class="form-help">Especifica el elemento que necesitas</div>
                                </div>
                                <div class="form-group">
                                    <label>🔢 Cantidad *</label>
                                    <input type="number" placeholder="1" required min="1">
                                    <div class="form-help">¿Cuántos necesitas?</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>💭 Observaciones</label>
                                <input type="text" placeholder="Urgente, marca específica, etc... (opcional)">
                                <div class="form-help">Información adicional sobre este elemento</div>
                            </div>
                            <div class="elemento-actions">
                                <button type="button" class="btn-remove-elemento" onclick="eliminarElementoSolicitud(this)" title="Eliminar elemento">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="add-elemento-section">
                        <button type="button" onclick="añadirElementoSolicitud()" class="btn btn-secondary">
                            ➕ Añadir Otro Elemento
                        </button>
                        <div class="form-help">Puedes solicitar múltiples elementos en una sola petición</div>
                    </div>
                </div>
                
                <!-- Sección de mensaje -->
                <div class="form-section">
                    <h3>💬 Mensaje para Coordinación</h3>
                    <div class="form-group">
                        <label for="mensaje-adicional">Información adicional</label>
                        <textarea id="mensaje-adicional" rows="4" placeholder="Contexto adicional: ¿Para qué necesitas estos elementos? ¿Hay alguna urgencia? ¿Preferencias de marca?"></textarea>
                        <div class="form-help">Este mensaje ayudará a coordinación a entender mejor tu solicitud</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('modal-solicitud')">
                        ✖️ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        📧 Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para historial -->
    <div id="modal-historial" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📝 Historial de Cambios</h2>
                <button class="modal-close" onclick="cerrarModal('modal-historial')">&times;</button>
            </div>
            
            <div id="historial-content">
                <div class="loading">🔄 Cargando historial...</div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let inventario = {};
        let filtroTexto = '';

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            cargarInventario();
            configurarEventos();
        });

        // Configurar eventos
        function configurarEventos() {
            // Búsqueda
            document.getElementById('search-input').addEventListener('input', function(e) {
                filtroTexto = e.target.value.toLowerCase();
                filtrarInventario();
            });

            // Los filtros de categoría se han eliminado para simplificar

            // Formularios
            document.getElementById('form-solicitud').addEventListener('submit', enviarSolicitud);

            // Cerrar modales al hacer clic fuera
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        cerrarModal(this.id);
                    }
                });
            });
        }

        // Cargar inventario desde API
        async function cargarInventario() {
            try {
                const response = await fetch('/api/botiquin?action=inventario');
                const data = await response.json();

                if (data.success) {
                    inventario = data.inventario;
                    renderizarInventario();
                } else {
                    mostrarError('Error cargando inventario: ' + data.error);
                }
            } catch (error) {
                mostrarError('Error de conexión: ' + error.message);
            }
        }



        // Renderizar inventario
        function renderizarInventario() {
            const container = document.getElementById('inventario-container');
            container.innerHTML = '';

            // Obtener todos los elementos sin categorías
            const todosElementos = Object.values(inventario).flat();
            
            // Filtrar por texto de búsqueda
            const elementosFiltrados = todosElementos.filter(elemento => 
                elemento.nombre_elemento.toLowerCase().includes(filtroTexto)
            );

            // Ordenar alfabéticamente por nombre
            elementosFiltrados.sort((a, b) => 
                a.nombre_elemento.localeCompare(b.nombre_elemento, 'es', { sensitivity: 'base' })
            );

            if (elementosFiltrados.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🏥</div>
                        <h3>No hay elementos en el inventario</h3>
                        <p>Los coordinadores pueden añadir elementos desde el panel de administración</p>
                        <p>Si necesitas material, puedes usar el botón <strong>"📧 Solicitar"</strong> para hacer una petición</p>
                    </div>
                `;
                return;
            }

            // Crear grid simple sin categorías
            const grid = document.createElement('div');
            grid.className = 'elementos-grid';
            grid.innerHTML = elementosFiltrados.map(elemento => crearTarjetaElemento(elemento)).join('');
            
            container.appendChild(grid);
        }

        // Crear tarjeta de elemento
        function crearTarjetaElemento(elemento) {
            const fechaActualizacion = elemento.fecha_ultima_actualizacion ? 
                new Date(elemento.fecha_ultima_actualizacion).toLocaleDateString() : 'N/A';
            
            return `
                <div class="elemento-card">
                    <div class="elemento-header">
                        <h4 class="elemento-nombre">${elemento.nombre_elemento}</h4>
                    </div>
                    
                    <div class="elemento-content">
                        <div class="cantidad-section">
                            <div class="cantidad-controls">
                                <button class="btn-cantidad btn-minus" onclick="cambiarCantidad(${elemento.id}, -1)" title="Reducir">-</button>
                                <div class="cantidad-display">
                                    <input type="number" class="cantidad-input" id="cantidad-${elemento.id}" 
                                           value="${elemento.cantidad_actual}" min="0" 
                                           onchange="actualizarCantidad(${elemento.id}, this.value)"
                                           onblur="this.classList.remove('editing')">
                                    <span class="cantidad-unidad">${elemento.unidad_medida}</span>
                                </div>
                                <button class="btn-cantidad btn-plus" onclick="cambiarCantidad(${elemento.id}, 1)" title="Aumentar">+</button>
                            </div>
                        </div>
                        
                        ${elemento.observaciones ? `
                            <div class="elemento-observaciones">
                                💬 ${elemento.observaciones}
                            </div>
                        ` : ''}
                        
                        <div class="elemento-meta">
                            <span>${fechaActualizacion} • ${elemento.ultima_actualizacion_por || 'Sistema'}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        // Filtrar inventario
        function filtrarInventario() {
            renderizarInventario();
        }







        // Cambiar cantidad con botones +/-
        async function cambiarCantidad(id, cambio) {
            const input = document.getElementById(`cantidad-${id}`);
            const nuevaCantidad = Math.max(0, parseInt(input.value) + cambio);
            input.value = nuevaCantidad;
            input.classList.add('editing');
            await actualizarCantidad(id, nuevaCantidad);
        }

        // Actualizar cantidad
        async function actualizarCantidad(id, nuevaCantidad) {
            const elemento = encontrarElementoPorId(id);
            if (!elemento) return;

            // Optimistic update
            const input = document.getElementById(`cantidad-${id}`);
            const valorAnterior = elemento.cantidad_actual;
            elemento.cantidad_actual = parseInt(nuevaCantidad);

            try {
                const response = await fetch('/api/botiquin?action=actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: id,
                        nombre_elemento: elemento.nombre_elemento,
                        categoria: elemento.categoria,
                        cantidad_actual: parseInt(nuevaCantidad),
                        unidad_medida: elemento.unidad_medida,
                        observaciones: elemento.observaciones
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    input.classList.remove('editing');
                    input.classList.add('success');
                    setTimeout(() => input.classList.remove('success'), 1000);
                } else {
                    // Revertir cambio
                    elemento.cantidad_actual = valorAnterior;
                    input.value = valorAnterior;
                    input.classList.remove('editing');
                    input.classList.add('error');
                    setTimeout(() => input.classList.remove('error'), 2000);
                    mostrarError(result.error);
                }
            } catch (error) {
                // Revertir cambio
                elemento.cantidad_actual = valorAnterior;
                input.value = valorAnterior;
                input.classList.remove('editing');
                input.classList.add('error');
                setTimeout(() => input.classList.remove('error'), 2000);
                mostrarError('Error de conexión: ' + error.message);
            }
        }



        // Ver historial
        async function verHistorial(id) {
            mostrarModal('modal-historial');
            document.getElementById('historial-content').innerHTML = '<div class="loading">🔄 Cargando historial...</div>';

            try {
                const response = await fetch(`/api/botiquin?action=historial&elemento_id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    renderizarHistorial(result.historial);
                } else {
                    document.getElementById('historial-content').innerHTML = 
                        `<div class="message error">${result.error}</div>`;
                }
            } catch (error) {
                document.getElementById('historial-content').innerHTML = 
                    `<div class="message error">Error de conexión: ${error.message}</div>`;
            }
        }

        // Renderizar historial
        function renderizarHistorial(historial) {
            const content = document.getElementById('historial-content');
            
            if (historial.length === 0) {
                content.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>Sin historial</h3>
                        <p>No hay cambios registrados para este elemento</p>
                    </div>
                `;
                return;
            }

            const historialHtml = historial.map(cambio => {
                const fecha = new Date(cambio.fecha_accion).toLocaleString();
                const accionEmoji = {
                    'creado': '➕',
                    'actualizado': '✏️',
                    'eliminado': '🗑️'
                };

                return `
                    <div class="historial-item">
                        <div class="historial-header">
                            <span class="historial-accion">${accionEmoji[cambio.accion]} ${cambio.accion.toUpperCase()}</span>
                            <span class="historial-fecha">${fecha}</span>
                        </div>
                        <div class="historial-usuario">👤 ${cambio.socorrista_nombre}</div>
                        ${cambio.cantidad_anterior !== null && cambio.cantidad_nueva !== null ? 
                            `<div class="historial-cantidad">📊 ${cambio.cantidad_anterior} → ${cambio.cantidad_nueva}</div>` : ''
                        }
                        ${cambio.observaciones ? 
                            `<div class="historial-observaciones">💬 ${cambio.observaciones}</div>` : ''
                        }
                    </div>
                `;
            }).join('');

            content.innerHTML = `
                <style>
                    .historial-item {
                        border: 2px solid #e0e0e0;
                        border-radius: 8px;
                        padding: 15px;
                        margin-bottom: 15px;
                    }
                    .historial-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 10px;
                        font-weight: bold;
                    }
                    .historial-accion {
                        color: #4caf50;
                    }
                    .historial-fecha {
                        color: #666;
                        font-size: 0.9em;
                    }
                    .historial-usuario,
                    .historial-cantidad,
                    .historial-observaciones {
                        margin: 5px 0;
                        font-size: 0.9em;
                        color: #666;
                    }
                </style>
                ${historialHtml}
            `;
        }

        // Mostrar modal solicitud
        function mostrarModalSolicitar() {
            document.getElementById('form-solicitud').reset();
            // Resetear elementos de solicitud
            const container = document.getElementById('elementos-solicitud');
            container.innerHTML = `
                <div class="elemento-solicitud-item">
                    <div class="form-row">
                        <div class="form-group">
                            <label>📦 Nombre del elemento *</label>
                            <input type="text" placeholder="Ej: Aspirinas 500mg, Vendas elásticas..." required>
                            <div class="form-help">Especifica el elemento que necesitas</div>
                        </div>
                        <div class="form-group">
                            <label>🔢 Cantidad *</label>
                            <input type="number" placeholder="1" required min="1">
                            <div class="form-help">¿Cuántos necesitas?</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>💭 Observaciones</label>
                        <input type="text" placeholder="Urgente, marca específica, etc... (opcional)">
                        <div class="form-help">Información adicional sobre este elemento</div>
                    </div>
                    <div class="elemento-actions">
                        <button type="button" class="btn-remove-elemento" onclick="eliminarElementoSolicitud(this)" title="Eliminar elemento">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            `;
            mostrarModal('modal-solicitud');
        }

        // Añadir elemento a solicitud
        function añadirElementoSolicitud() {
            const container = document.getElementById('elementos-solicitud');
            const nuevoElemento = document.createElement('div');
            nuevoElemento.className = 'elemento-solicitud-item';
            nuevoElemento.innerHTML = `
                <div class="form-row">
                    <div class="form-group">
                        <label>📦 Nombre del elemento *</label>
                        <input type="text" placeholder="Ej: Aspirinas 500mg, Vendas elásticas..." required>
                        <div class="form-help">Especifica el elemento que necesitas</div>
                    </div>
                    <div class="form-group">
                        <label>🔢 Cantidad *</label>
                        <input type="number" placeholder="1" required min="1">
                        <div class="form-help">¿Cuántos necesitas?</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>💭 Observaciones</label>
                    <input type="text" placeholder="Urgente, marca específica, etc... (opcional)">
                    <div class="form-help">Información adicional sobre este elemento</div>
                </div>
                <div class="elemento-actions">
                    <button type="button" class="btn-remove-elemento" onclick="eliminarElementoSolicitud(this)" title="Eliminar elemento">
                        🗑️ Eliminar
                    </button>
                </div>
            `;
            container.appendChild(nuevoElemento);
        }

        // Eliminar elemento de solicitud
        function eliminarElementoSolicitud(button) {
            const container = document.getElementById('elementos-solicitud');
            if (container.children.length > 1) {
                button.parentElement.parentElement.remove();
            } else {
                mostrarError('Debe tener al menos un elemento en la solicitud');
            }
        }

        // Enviar solicitud
        async function enviarSolicitud(e) {
            e.preventDefault();
            
            const elementosDiv = document.querySelectorAll('.elemento-solicitud-item');
            const elementos = [];
            
            elementosDiv.forEach(div => {
                const inputs = div.querySelectorAll('input');
                const nombre = inputs[0].value.trim();
                const cantidad = parseInt(inputs[1].value);
                const observaciones = inputs[2].value.trim();
                
                if (nombre && cantidad > 0) {
                    elementos.push({ nombre, cantidad, observaciones });
                }
            });
            
            if (elementos.length === 0) {
                mostrarError('Debe añadir al menos un elemento a la solicitud');
                return;
            }
            
            const solicitudData = {
                elementos: elementos,
                mensaje_adicional: document.getElementById('mensaje-adicional').value.trim()
            };

            try {
                const response = await fetch('/api/botiquin?action=solicitar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(solicitudData)
                });

                const result = await response.json();
                
                if (result.success) {
                    mostrarExito('📧 ' + result.message);
                    cerrarModal('modal-solicitud');
                } else {
                    mostrarError(result.error);
                }
            } catch (error) {
                mostrarError('Error de conexión: ' + error.message);
            }
        }

        // Utilidades
        function encontrarElementoPorId(id) {
            for (const categoria of Object.values(inventario)) {
                const elemento = categoria.find(e => e.id == id);
                if (elemento) return elemento;
            }
            return null;
        }

        function mostrarModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function mostrarExito(mensaje) {
            mostrarMensaje(mensaje, 'success');
        }

        function mostrarError(mensaje) {
            mostrarMensaje(mensaje, 'error');
        }

        function mostrarMensaje(mensaje, tipo) {
            // Remover mensajes existentes
            document.querySelectorAll('.message').forEach(m => m.remove());
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${tipo}`;
            messageDiv.textContent = mensaje;
            
            const container = document.querySelector('.botiquin-container');
            container.insertBefore(messageDiv, container.firstChild);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
            
            // Scroll al mensaje
            messageDiv.scrollIntoView({ behavior: 'smooth' });
        }

        // Los estilos de elemento-solicitud están ahora en assets/css/styles.css
    </script>

    <?php include __DIR__ . '/../partials/footer-navigation.php'; ?>
</body>
</html>