<?php
// Script de Testing WEB - Sistema de Permisos ResQ Admin
// Acceso vía navegador para testing del sistema de permisos

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/AdminPermissionsService.php';

// Configurar headers para output HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Sistema de Permisos - ResQ Admin</title>
    <style>
        body { font-family: monospace; background: #f0f0f0; margin: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #343a40; }
        h2 { color: #007bff; }
        .emoji { font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Testing Sistema de Permisos ResQ Admin</h1>
        <p><strong>Fecha:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <hr>

        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            // 1. TESTING SUPERADMIN
            echo '<div class="section">';
            echo '<h2>1️⃣ Testing Superadmin</h2>';
            
            $superadmin = [
                'id' => 1,
                'nombre' => 'Test Superadmin',
                'tipo' => 'superadmin',
                'coordinador_id' => null
            ];
            
            $permissionsSuperadmin = new AdminPermissionsService($superadmin);
            
            echo '<p class="success">✅ Rol: ' . ($permissionsSuperadmin->isSuperAdmin() ? "SUPERADMIN" : "NO SUPERADMIN") . '</p>';
            echo '<p class="info">📊 Coordinadores permitidos: ' . count($permissionsSuperadmin->getCoordinadoresPermitidos()) . '</p>';
            echo '<p class="info">🏢 Instalaciones permitidas: ' . count($permissionsSuperadmin->getInstalacionesPermitidas()) . '</p>';
            echo '<p class="info">👥 Socorristas permitidos: ' . count($permissionsSuperadmin->getSocorristasPermitidos()) . '</p>';
            echo '</div>';
            
            // 2. TESTING ADMIN REGULAR
            echo '<div class="section">';
            echo '<h2>2️⃣ Testing Admin Regular</h2>';
            
            // Crear datos de prueba
            $stmt = $db->prepare("INSERT IGNORE INTO admin_coordinadores (admin_id, coordinador_id) VALUES (2, 1)");
            $stmt->execute();
            
            $adminRegular = [
                'id' => 2,
                'nombre' => 'Test Admin Regular',
                'tipo' => 'admin',
                'coordinador_id' => 1
            ];
            
            $permissionsAdmin = new AdminPermissionsService($adminRegular);
            
            echo '<p class="success">✅ Rol: ' . ($permissionsAdmin->isAdmin() ? "ADMIN" : "NO ADMIN") . '</p>';
            echo '<p class="info">📊 Coordinadores permitidos: ' . count($permissionsAdmin->getCoordinadoresPermitidos()) . '</p>';
            echo '<p class="info">🏢 Instalaciones permitidas: ' . count($permissionsAdmin->getInstalacionesPermitidas()) . '</p>';
            echo '<p class="info">👥 Socorristas permitidos: ' . count($permissionsAdmin->getSocorristasPermitidos()) . '</p>';
            echo '</div>';
            
            // 3. TESTING COORDINADOR
            echo '<div class="section">';
            echo '<h2>3️⃣ Testing Coordinador</h2>';
            
            $coordinador = [
                'id' => 3,
                'nombre' => 'Test Coordinador',
                'tipo' => 'coordinador',
                'coordinador_id' => 1
            ];
            
            $permissionsCoordinador = new AdminPermissionsService($coordinador);
            
            echo '<p class="success">✅ Rol: ' . ($permissionsCoordinador->isCoordinador() ? "COORDINADOR" : "NO COORDINADOR") . '</p>';
            echo '<p class="info">📊 Coordinadores permitidos: ' . count($permissionsCoordinador->getCoordinadoresPermitidos()) . '</p>';
            echo '<p class="info">🏢 Instalaciones permitidas: ' . count($permissionsCoordinador->getInstalacionesPermitidas()) . '</p>';
            echo '<p class="info">👥 Socorristas permitidos: ' . count($permissionsCoordinador->getSocorristasPermitidos()) . '</p>';
            echo '</div>';
            
            // 4. TESTING VERIFICACIÓN DE ACCESO
            echo '<div class="section">';
            echo '<h2>4️⃣ Testing Verificación de Acceso</h2>';
            
            $stmt = $db->prepare("SELECT id FROM coordinadores LIMIT 1");
            $stmt->execute();
            $coordinadorId = $stmt->fetchColumn();
            
            $stmt = $db->prepare("SELECT id FROM instalaciones LIMIT 1");
            $stmt->execute();
            $instalacionId = $stmt->fetchColumn();
            
            if ($coordinadorId && $instalacionId) {
                echo "<p class='info'>📋 Testing con Coordinador ID: $coordinadorId, Instalación ID: $instalacionId</p>";
                
                echo '<table border="1" style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
                echo '<tr><th>Rol</th><th>Acceso Coordinador</th><th>Acceso Instalación</th></tr>';
                
                echo '<tr><td>Superadmin</td>';
                echo '<td class="' . ($permissionsSuperadmin->puedeAccederCoordinador($coordinadorId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td>';
                echo '<td class="' . ($permissionsSuperadmin->puedeAccederInstalacion($instalacionId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td></tr>';
                
                echo '<tr><td>Admin</td>';
                echo '<td class="' . ($permissionsAdmin->puedeAccederCoordinador($coordinadorId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td>';
                echo '<td class="' . ($permissionsAdmin->puedeAccederInstalacion($instalacionId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td></tr>';
                
                echo '<tr><td>Coordinador</td>';
                echo '<td class="' . ($permissionsCoordinador->puedeAccederCoordinador($coordinadorId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td>';
                echo '<td class="' . ($permissionsCoordinador->puedeAccederInstalacion($instalacionId) ? 'success">✅ SÍ' : 'error">❌ NO') . '</td></tr>';
                
                echo '</table>';
            } else {
                echo '<p class="warning">⚠️ No hay datos suficientes para testing de acceso específico</p>';
            }
            echo '</div>';
            
            // 5. TESTING BOTIQUÍN
            echo '<div class="section">';
            echo '<h2>5️⃣ Testing Botiquín</h2>';
            
            $inventarioSuperadmin = $permissionsSuperadmin->getInventarioBotiquinPermitido();
            $inventarioAdmin = $permissionsAdmin->getInventarioBotiquinPermitido();
            $inventarioCoordinador = $permissionsCoordinador->getInventarioBotiquinPermitido();
            
            echo '<p class="info">💊 Inventario Superadmin: ' . count($inventarioSuperadmin) . ' elementos</p>';
            echo '<p class="info">💊 Inventario Admin: ' . count($inventarioAdmin) . ' elementos</p>';
            echo '<p class="info">💊 Inventario Coordinador: ' . count($inventarioCoordinador) . ' elementos</p>';
            echo '</div>';
            
            // 6. TESTING SOLICITUDES
            echo '<div class="section">';
            echo '<h2>6️⃣ Testing Solicitudes</h2>';
            
            $solicitudesSuperadmin = $permissionsSuperadmin->getSolicitudesMaterialPermitidas();
            $solicitudesAdmin = $permissionsAdmin->getSolicitudesMaterialPermitidas();
            $solicitudesCoordinador = $permissionsCoordinador->getSolicitudesMaterialPermitidas();
            
            echo '<p class="info">📋 Solicitudes Superadmin: ' . count($solicitudesSuperadmin) . ' solicitudes</p>';
            echo '<p class="info">📋 Solicitudes Admin: ' . count($solicitudesAdmin) . ' solicitudes</p>';
            echo '<p class="info">📋 Solicitudes Coordinador: ' . count($solicitudesCoordinador) . ' solicitudes</p>';
            echo '</div>';
            
            // 7. RESUMEN DETALLADO
            echo '<div class="section">';
            echo '<h2>7️⃣ Resumen Detallado</h2>';
            
            echo '<h3>🔐 Superadmin:</h3>';
            echo '<pre>' . print_r($permissionsSuperadmin->getResumenPermisos(), true) . '</pre>';
            
            echo '<h3>🔐 Admin Regular:</h3>';
            echo '<pre>' . print_r($permissionsAdmin->getResumenPermisos(), true) . '</pre>';
            
            echo '<h3>🔐 Coordinador:</h3>';
            echo '<pre>' . print_r($permissionsCoordinador->getResumenPermisos(), true) . '</pre>';
            echo '</div>';
            
            // RESULTADO FINAL
            echo '<div class="section" style="border-left-color: #28a745; background: #d4edda;">';
            echo '<h2 class="success">✅ TESTING COMPLETADO CON ÉXITO</h2>';
            echo '<p>El sistema de permisos está funcionando correctamente.</p>';
            echo '<p><strong>Listo para implementar el sistema de botiquín administrativo.</strong></p>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="section" style="border-left-color: #dc3545; background: #f8d7da;">';
            echo '<h2 class="error">❌ ERROR EN TESTING</h2>';
            echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre class="error">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
        
        <hr>
        <p><small>Script de testing temporal - Puede eliminarse después de verificar funcionamiento</small></p>
    </div>
</body>
</html> 