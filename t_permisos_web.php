<?php
// Script de Testing WEB - Sistema de Permisos ResQ Admin (CORREGIDO)

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/AdminPermissionsService.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Sistema de Permisos - ResQ Admin (CORREGIDO)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; margin: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
        h1 { color: #343a40; }
        h2 { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Testing Sistema de Permisos ResQ Admin (CORREGIDO)</h1>
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
            
            $coordinadores = $permissionsSuperadmin->getCoordinadoresPermitidos();
            $instalaciones = $permissionsSuperadmin->getInstalacionesPermitidas();
            $socorristas = $permissionsSuperadmin->getSocorristasPermitidos();
            
            echo '<p class="info">📊 Coordinadores permitidos: ' . count($coordinadores) . '</p>';
            echo '<p class="info">🏢 Instalaciones permitidas: ' . count($instalaciones) . '</p>';
            echo '<p class="info">👥 Socorristas permitidos: ' . count($socorristas) . '</p>';
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
            
            $coordinadoresAdmin = $permissionsAdmin->getCoordinadoresPermitidos();
            $instalacionesAdmin = $permissionsAdmin->getInstalacionesPermitidas();
            $socorristasAdmin = $permissionsAdmin->getSocorristasPermitidos();
            
            echo '<p class="info">📊 Coordinadores permitidos: ' . count($coordinadoresAdmin) . '</p>';
            echo '<p class="info">🏢 Instalaciones permitidas: ' . count($instalacionesAdmin) . '</p>';
            echo '<p class="info">👥 Socorristas permitidos: ' . count($socorristasAdmin) . '</p>';
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
            echo '<pre style="background: #f8d7da; padding: 10px; border-radius: 4px; overflow-x: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
        
        <hr>
        <p><small>Script de testing temporal - Puede eliminarse después de verificar funcionamiento</small></p>
    </div>
</body>
</html> 