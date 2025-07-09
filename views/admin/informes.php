<?php
// Vista de Informes Admin para ResQ
require_once __DIR__ . '/../../classes/AdminAuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';

$adminAuth = new AdminAuthService();
$adminService = new AdminService();

// Verificar autenticación admin
if (!$adminAuth->estaAutenticadoAdmin()) {
    header('Location: /admin');
    exit;
}

// Obtener datos para los filtros
$admin = $adminAuth->getAdminActual();
$instalaciones = $adminService->getInstalaciones();
$socorristas = $adminService->getSocorristas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes y Exportación - ResQ Admin</title>
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
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/admin">Dashboard</a> 
            <span class="breadcrumb-separator">→</span> 
            <span class="breadcrumb-current">Informes</span>
        </div>
        
        <div class="admin-header-section">
            <h1>📊 Informes y Exportación</h1>
            <p>Exporta datos de formularios a Excel con filtros personalizados</p>
        </div>
        
        <!-- Sección de Filtros -->
        <div class="filters-section">
            <h2>🔍 Filtros de Exportación</h2>
            
            <div class="filters-grid">
                <!-- Filtro de Fechas -->
                <div class="filter-group">
                    <label for="fecha_inicio">📅 Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
                </div>
                
                <div class="filter-group">
                    <label for="fecha_fin">📅 Fecha Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
                </div>
                
                <!-- Filtro de Instalación -->
                <div class="filter-group">
                    <label for="instalacion_id">🏢 Instalación</label>
                    <select id="instalacion_id" name="instalacion_id" class="form-control">
                        <option value="">Todas las instalaciones</option>
                        <?php foreach ($instalaciones as $instalacion): ?>
                            <option value="<?= $instalacion['id'] ?>">
                                <?= htmlspecialchars($instalacion['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Filtro de Socorrista -->
                <div class="filter-group">
                    <label for="socorrista_id">🚑 Socorrista</label>
                    <select id="socorrista_id" name="socorrista_id" class="form-control">
                        <option value="">Todos los socorristas</option>
                        <?php foreach ($socorristas as $socorrista): ?>
                            <option value="<?= $socorrista['id'] ?>">
                                <?= htmlspecialchars($socorrista['nombre']) ?> (<?= htmlspecialchars($socorrista['dni']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Botón para limpiar filtros -->
            <div class="filter-actions">
                <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                    🧹 Limpiar Filtros
                </button>
            </div>
        </div>
        
        <!-- Sección de Exportación -->
        <div class="export-section">
            <h2>📥 Exportar Datos</h2>
            
            <div class="export-cards">
                <!-- Control de Flujo -->
                <div class="export-card">
                    <div class="export-card-header">
                        <div class="export-icon">👥</div>
                        <h3>Control de Flujo</h3>
                    </div>
                    <div class="export-card-content">
                        <p>Exporta registros de control de aforo con cálculo de porcentajes de ocupación.</p>
                        <div class="export-fields">
                            <strong>Campos incluidos:</strong> Fecha, Hora, Instalación, Socorrista, Espacio, Personas, Aforo Máximo, Porcentaje Ocupación, Observaciones
                        </div>
                    </div>
                    <div class="export-card-actions">
                        <button class="btn btn-primary" onclick="exportarDatosV3('control_flujo')">
                            📊 Exportar Control de Flujo
                        </button>
                    </div>
                </div>
                
                <!-- Incidencias -->
                <div class="export-card">
                    <div class="export-card-header">
                        <div class="export-icon">⚠️</div>
                        <h3>Incidencias</h3>
                    </div>
                    <div class="export-card-content">
                        <p>Exporta todas las incidencias registradas con detalles completos.</p>
                        <div class="export-fields">
                            <strong>Campos incluidos:</strong> Fecha, Hora, Instalación, Socorrista, Tipo Incidencia, Descripción, Gravedad, Personas Afectadas, Observaciones
                        </div>
                    </div>
                    <div class="export-card-actions">
                        <button class="btn btn-primary" onclick="exportarDatosV3('incidencias')">
                            ⚠️ Exportar Incidencias
                        </button>
                    </div>
                </div>
                

            </div>
        </div>
        
        <!-- Estado de exportación -->
        <div id="export-status" class="export-status" style="display: none;">
            <div class="loading-spinner">
                <div class="loading-spinner-content">
                    <div class="loading-spinner-icon"></div>
                    <div class="loading-spinner-text">Preparando exportación...</div>
                </div>
            </div>
            <span id="export-message">Preparando exportación...</span>
        </div>
    </div>
    
    <script>
        // Funciones JavaScript para la exportación
        function exportarDatosV3(tipo) {
            // Mostrar estado de carga
            const statusDiv = document.getElementById('export-status');
            const messageSpan = document.getElementById('export-message');
            statusDiv.style.display = 'flex';
            messageSpan.textContent = 'Preparando exportación...';
            
            // Obtener filtros
            const filtros = obtenerFiltros();
            
            // Construir URL de exportación
            let url = `/admin/api/informes?action=export_${tipo}&v=${Date.now()}`;
            
            // Añadir filtros a la URL
            const params = new URLSearchParams();
            if (filtros.fecha_inicio) params.append('fecha_inicio', filtros.fecha_inicio);
            if (filtros.fecha_fin) params.append('fecha_fin', filtros.fecha_fin);
            if (filtros.instalacion_id) params.append('instalacion_id', filtros.instalacion_id);
            if (filtros.socorrista_id) params.append('socorrista_id', filtros.socorrista_id);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            // Actualizar mensaje
            messageSpan.textContent = 'Descargando archivo...';
            
            // Crear enlace temporal para descarga
            const link = document.createElement('a');
            link.href = url;
            link.download = `${tipo}_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Ocultar estado después de un breve delay
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 2000);
        }
        
        function obtenerFiltros() {
            return {
                fecha_inicio: document.getElementById('fecha_inicio').value,
                fecha_fin: document.getElementById('fecha_fin').value,
                instalacion_id: document.getElementById('instalacion_id').value,
                socorrista_id: document.getElementById('socorrista_id').value
            };
        }
        
        function limpiarFiltros() {
            document.getElementById('fecha_inicio').value = '';
            document.getElementById('fecha_fin').value = '';
            document.getElementById('instalacion_id').value = '';
            document.getElementById('socorrista_id').value = '';
        }
        
        // Establecer fecha por defecto (último mes)
        window.addEventListener('DOMContentLoaded', function() {
            const hoy = new Date();
            const haceUnMes = new Date();
            haceUnMes.setMonth(hoy.getMonth() - 1);
            
            document.getElementById('fecha_inicio').value = haceUnMes.toISOString().split('T')[0];
            document.getElementById('fecha_fin').value = hoy.toISOString().split('T')[0];
        });
    </script>
</body>
</html> 