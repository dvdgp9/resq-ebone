<?php
// Vista del Dashboard Admin para ResQ
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';

$adminAuth = new AdminAuthService();
$adminService = new AdminService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

// Obtener datos del admin actual y estadísticas
$admin = $adminAuth->getAdminActual();
$stats = $adminService->getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ResQ</title>
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
        <div class="admin-welcome">
            <h1>🎛️ Panel de Administración</h1>
            <p>Bienvenido, <?= htmlspecialchars($admin['nombre']) ?>. Gestiona coordinadores, instalaciones y socorristas desde aquí.</p>
        </div>
        
        <!-- Cards de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>Coordinadores</h3>
                    <div class="stat-number"><?= $stats['coordinadores'] ?? 0 ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <h3>Instalaciones</h3>
                    <div class="stat-number"><?= $stats['instalaciones'] ?? 0 ?></div>
                    <div class="stat-label">Activas</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚑</div>
                <div class="stat-content">
                    <h3>Socorristas</h3>
                    <div class="stat-number"><?= $stats['socorristas'] ?? 0 ?></div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3>Formularios</h3>
                    <div class="stat-number"><?= $stats['formularios_mes'] ?? 0 ?></div>
                    <div class="stat-label">Este mes</div>
                </div>
            </div>
        </div>
        
        <!-- Navegación Principal -->
        <div class="admin-nav-grid">
            <a href="/admin/coordinadores" class="admin-nav-card">
                <div class="nav-card-icon">👥</div>
                <h3>Gestionar Coordinadores</h3>
                <p>Crear, editar y gestionar coordinadores del sistema</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/instalaciones" class="admin-nav-card">
                <div class="nav-card-icon">🏢</div>
                <h3>Gestionar Instalaciones</h3>
                <p>Administrar instalaciones y asignar coordinadores</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/socorristas" class="admin-nav-card">
                <div class="nav-card-icon">🚑</div>
                <h3>Gestionar Socorristas</h3>
                <p>Crear socorristas y asignarlos a instalaciones</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/informes" class="admin-nav-card">
                <div class="nav-card-icon">📊</div>
                <h3>Informes y Exportación</h3>
                <p>Exportar formularios a Excel con filtros personalizados</p>
                <div class="nav-card-arrow">→</div>
            </a>
            
            <a href="/admin/botiquin" class="admin-nav-card">
                <div class="nav-card-icon">🏥</div>
                <h3>Gestión de Botiquín</h3>
                <p>Administrar inventarios de botiquín y solicitudes de material</p>
                <div class="nav-card-arrow">→</div>
            </a>
        </div>
        
        <!-- DEBUG: Verificar tipo de admin -->
        <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
            <strong>🔧 DEBUG ADMIN:</strong> 
            Tipo = "<?= htmlspecialchars($admin['tipo']) ?>" | 
            Es igual a 'superadmin' = <?= $admin['tipo'] === 'superadmin' ? 'SÍ' : 'NO' ?> |
            Condición cumplida = <?= ($admin['tipo'] === 'superadmin') ? 'SÍ - DEBERÍA APARECER' : 'NO - POR ESO NO APARECE' ?>
        </div>
        
        <?php if ($admin['tipo'] === 'superadmin'): ?>
        <!-- Gestión de Administradores (Solo Superadmin) -->
        <div class="admin-section">
            <div class="section-header">
                <h2>🔐 Gestión de Administradores</h2>
                <button class="btn btn-primary" onclick="abrirModalAdministrador()">
                    ➕ Nuevo Administrador
                </button>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table" id="tabla-administradores">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Coordinadores Asignados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="administradores-tbody">
                        <tr>
                            <td colspan="6" class="loading">⏳ Cargando administradores...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Accesos Rápidos -->
        <div class="quick-actions">
            <h2>⚡ Acciones Rápidas</h2>
            <div class="quick-actions-grid">
                <button class="btn btn-primary" onclick="window.location.href='/admin/coordinadores?action=create'">
                    ➕ Nuevo Coordinador
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/instalaciones?action=create'">
                    🏢 Nueva Instalación
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/socorristas?action=create'">
                    🚑 Nuevo Socorrista
                </button>
                <button class="btn btn-primary" onclick="window.location.href='/admin/botiquin'">
                    🏥 Gestión Botiquín
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='/'">
                    🚨 Ir a Panel Socorristas
                </button>
            </div>
        </div>
    </div>
    
    <?php if ($admin['tipo'] === 'superadmin'): ?>
    <!-- Modales para Gestión de Administradores -->
    
    <!-- Modal Crear/Editar Administrador -->
    <div id="modal-administrador" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-administrador-titulo">➕ Nuevo Administrador</h2>
                <span class="close" onclick="cerrarModalAdministrador()">&times;</span>
            </div>
            
            <form id="form-administrador" onsubmit="return false;">
                <input type="hidden" id="admin-id" name="id">
                
                <div class="form-group">
                    <label for="admin-nombre">Nombre *</label>
                    <input type="text" id="admin-nombre" name="nombre" required 
                           placeholder="Nombre completo del administrador">
                </div>
                
                <div class="form-group">
                    <label for="admin-email">Email *</label>
                    <input type="email" id="admin-email" name="email" required 
                           placeholder="email@ejemplo.com">
                </div>
                
                <div class="form-group">
                    <label for="admin-password">Password *</label>
                    <input type="password" id="admin-password" name="password" 
                           placeholder="Mínimo 8 caracteres, mayúscula + minúscula">
                    <small class="form-help">Dejar vacío para mantener password actual (solo edición)</small>
                </div>
                
                <div class="form-group">
                    <label for="admin-tipo">Tipo de Administrador *</label>
                    <select id="admin-tipo" name="tipo" required onchange="toggleCoordinadoresSection()">
                        <option value="">Selecciona tipo</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                
                <div class="form-group" id="coordinadores-section" style="display: none;">
                    <label for="admin-coordinadores">Coordinadores Asignados</label>
                    <div id="coordinadores-list" class="checkbox-list">
                        <div class="loading">⏳ Cargando coordinadores...</div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalAdministrador()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-admin">
                        💾 Guardar Administrador
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Confirmación Desactivar -->
    <div id="modal-confirmar-desactivar" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2>⚠️ Confirmar Desactivación</h2>
                <span class="close" onclick="cerrarModalConfirmar()">&times;</span>
            </div>
            
            <div class="modal-body">
                <p>¿Estás seguro que deseas desactivar al administrador <strong id="admin-nombre-confirmar"></strong>?</p>
                <p class="warning">Esta acción no se puede deshacer.</p>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalConfirmar()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-desactivar">
                    🗑️ Desactivar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Variables globales
    let administradores = [];
    let coordinadores = [];
    let adminEditando = null;
    let adminDesactivando = null;

    // Inicializar cuando carga la página
    document.addEventListener('DOMContentLoaded', function() {
        cargarAdministradores();
        cargarCoordinadores();
    });

    // Cargar lista de administradores
    async function cargarAdministradores() {
        try {
            const response = await fetch('/controllers/admin/administradores.php?action=listar', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                administradores = data.administradores;
                actualizarTablaAdministradores();
            } else {
                mostrarError('Error al cargar administradores: ' + data.error);
            }
        } catch (error) {
            mostrarError('Error de conexión al cargar administradores');
            console.error(error);
        }
    }

    // Cargar lista de coordinadores
    async function cargarCoordinadores() {
        try {
            const response = await fetch('/controllers/admin/administradores.php?action=coordinadores', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                coordinadores = data.coordinadores;
                actualizarListaCoordinadores();
            } else {
                console.error('Error al cargar coordinadores:', data.error);
            }
        } catch (error) {
            console.error('Error de conexión al cargar coordinadores:', error);
        }
    }

    // Actualizar tabla de administradores
    function actualizarTablaAdministradores() {
        const tbody = document.getElementById('administradores-tbody');
        
        if (administradores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty">No hay administradores registrados</td></tr>';
            return;
        }
        
        tbody.innerHTML = administradores.map(admin => `
            <tr>
                <td>${admin.nombre}</td>
                <td>${admin.email}</td>
                <td>
                    <span class="badge badge-${admin.tipo === 'superadmin' ? 'primary' : 'secondary'}">
                        ${admin.tipo === 'superadmin' ? '🔐 Super Admin' : '👤 Admin'}
                    </span>
                </td>
                <td>
                    ${admin.coordinadores_asignados || 'Ninguno'}
                    <small class="text-muted">(${admin.total_coordinadores || 0})</small>
                </td>
                <td>
                    <span class="badge badge-${admin.activo == 1 ? 'success' : 'danger'}">
                        ${admin.activo == 1 ? '✅ Activo' : '❌ Inactivo'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-small btn-secondary" 
                                onclick="editarAdministrador(${admin.id})" 
                                title="Editar">
                            ✏️
                        </button>
                        ${admin.activo == 1 ? `
                        <button class="btn btn-small btn-danger" 
                                onclick="confirmarDesactivar(${admin.id}, '${admin.nombre}')" 
                                title="Desactivar">
                            🗑️
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }



    // Actualizar lista de coordinadores en modal
    function actualizarListaCoordinadores() {
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

    // Abrir modal para nuevo administrador
    function abrirModalAdministrador() {
        adminEditando = null;
        document.getElementById('modal-administrador-titulo').textContent = '➕ Nuevo Administrador';
        document.getElementById('form-administrador').reset();
        document.getElementById('admin-id').value = '';
        document.getElementById('admin-password').required = true;
        document.getElementById('btn-guardar-admin').textContent = '💾 Crear Administrador';
        
        // Limpiar coordinadores seleccionados
        document.querySelectorAll('#coordinadores-list input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        toggleCoordinadoresSection();
        document.getElementById('modal-administrador').style.display = 'block';
    }

    // Editar administrador existente
    async function editarAdministrador(adminId) {
        try {
            const response = await fetch(`/controllers/admin/administradores.php?action=administrador&id=${adminId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.success) {
                adminEditando = data.administrador;
                
                document.getElementById('modal-administrador-titulo').textContent = '✏️ Editar Administrador';
                document.getElementById('admin-id').value = adminEditando.id;
                document.getElementById('admin-nombre').value = adminEditando.nombre;
                document.getElementById('admin-email').value = adminEditando.email;
                document.getElementById('admin-tipo').value = adminEditando.tipo;
                document.getElementById('admin-password').required = false;
                document.getElementById('btn-guardar-admin').textContent = '💾 Actualizar Administrador';
                
                // Marcar coordinadores asignados
                document.querySelectorAll('#coordinadores-list input[type="checkbox"]').forEach(cb => {
                    cb.checked = adminEditando.coordinadores_asignados.some(coord => coord.id == cb.value);
                });
                
                toggleCoordinadoresSection();
                document.getElementById('modal-administrador').style.display = 'block';
            } else {
                mostrarError('Error al cargar administrador: ' + data.error);
            }
        } catch (error) {
            mostrarError('Error de conexión al cargar administrador');
            console.error(error);
        }
    }

    // Cerrar modal administrador
    function cerrarModalAdministrador() {
        document.getElementById('modal-administrador').style.display = 'none';
        adminEditando = null;
    }

    // Toggle sección coordinadores según tipo
    function toggleCoordinadoresSection() {
        const tipo = document.getElementById('admin-tipo').value;
        const section = document.getElementById('coordinadores-section');
        
        if (tipo === 'admin') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    }

    // Guardar administrador (crear o editar)
    document.getElementById('form-administrador').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Obtener coordinadores seleccionados
        const coordinadoresSeleccionados = Array.from(
            document.querySelectorAll('#coordinadores-list input[type="checkbox"]:checked')
        ).map(cb => parseInt(cb.value));
        
        data.coordinadores = coordinadoresSeleccionados;
        
        const action = adminEditando ? 'actualizar' : 'crear';
        const btnGuardar = document.getElementById('btn-guardar-admin');
        
        btnGuardar.disabled = true;
        btnGuardar.textContent = '⏳ Guardando...';
        
        try {
            const response = await fetch(`/controllers/admin/administradores.php?action=${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarExito(result.message);
                cerrarModalAdministrador();
                await cargarAdministradores();
            } else {
                mostrarError(result.error);
            }
        } catch (error) {
            mostrarError('Error de conexión al guardar administrador');
            console.error(error);
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = adminEditando ? '💾 Actualizar Administrador' : '💾 Crear Administrador';
        }
    });

    // Confirmar desactivación
    function confirmarDesactivar(adminId, adminNombre) {
        adminDesactivando = adminId;
        document.getElementById('admin-nombre-confirmar').textContent = adminNombre;
        document.getElementById('modal-confirmar-desactivar').style.display = 'block';
    }

    // Cerrar modal confirmar
    function cerrarModalConfirmar() {
        document.getElementById('modal-confirmar-desactivar').style.display = 'none';
        adminDesactivando = null;
    }

    // Desactivar administrador
    document.getElementById('btn-confirmar-desactivar').addEventListener('click', async function() {
        if (!adminDesactivando) return;
        
        this.disabled = true;
        this.textContent = '⏳ Desactivando...';
        
        try {
            const response = await fetch('/controllers/admin/administradores.php?action=desactivar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ id: adminDesactivando })
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarExito(result.message);
                cerrarModalConfirmar();
                await cargarAdministradores();
            } else {
                mostrarError(result.error);
            }
        } catch (error) {
            mostrarError('Error de conexión al desactivar administrador');
            console.error(error);
        } finally {
            this.disabled = false;
            this.textContent = '🗑️ Desactivar';
        }
    });

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const modalAdmin = document.getElementById('modal-administrador');
        const modalConfirmar = document.getElementById('modal-confirmar-desactivar');
        
        if (event.target === modalAdmin) {
            cerrarModalAdministrador();
        } else if (event.target === modalConfirmar) {
            cerrarModalConfirmar();
        }
    }

    // Funciones de utilidad para mostrar mensajes
    function mostrarExito(mensaje) {
        // Reutilizar sistema de notificaciones existente o crear uno simple
        alert('✅ ' + mensaje);
    }

    function mostrarError(mensaje) {
        // Reutilizar sistema de notificaciones existente o crear uno simple
        alert('❌ ' + mensaje);
    }
    </script>
    <?php endif; ?>

</body>
</html> 