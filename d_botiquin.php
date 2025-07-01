<?php
// Archivo de debugging para probar el API de botiquín
session_start();

echo "<h1>🚨 Debug Botiquín API</h1>";

// Información de sesión
echo "<h2>📋 Información de Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Probar carga de clases
echo "<h2>🔧 Carga de Clases:</h2>";
try {
    require_once 'config/app.php';
    require_once 'config/database.php';
    echo "✅ Configuración cargada<br>";
    
    require_once 'classes/AdminAuthService.php';
    echo "✅ AdminAuthService cargado<br>";
    
    $adminAuth = new AdminAuthService();
    echo "✅ AdminAuthService instanciado<br>";
    
    echo "<strong>¿Está autenticado?</strong> " . ($adminAuth->estaAutenticadoAdmin() ? "✅ SÍ" : "❌ NO") . "<br>";
    
    if ($adminAuth->estaAutenticadoAdmin()) {
        $admin = $adminAuth->getAdminActual();
        echo "<strong>Admin actual:</strong> " . $admin['nombre'] . " (" . $admin['tipo'] . ")<br>";
        
        $permissions = $adminAuth->getPermissionsService();
        echo "✅ Permisos cargados<br>";
        
        echo "<h3>🔐 Resumen de Permisos:</h3>";
        echo "<pre>";
        print_r($permissions->getResumenPermisos());
        echo "</pre>";
        
        echo "<h3>🏢 Instalaciones Permitidas:</h3>";
        echo "<pre>";
        print_r($permissions->getInstalacionesPermitidas());
        echo "</pre>";
        
        echo "<h3>📦 Inventario Botiquín:</h3>";
        $inventario = $permissions->getInventarioBotiquinPermitido();
        echo "Total elementos: " . count($inventario) . "<br>";
        if (count($inventario) > 0) {
            echo "<pre>";
            print_r(array_slice($inventario, 0, 2)); // Solo primeros 2 elementos
            echo "</pre>";
        }
        
        echo "<h3>📋 Solicitudes Material:</h3>";
        $solicitudes = $permissions->getSolicitudesMaterialPermitidas();
        echo "Total solicitudes: " . count($solicitudes) . "<br>";
        if (count($solicitudes) > 0) {
            echo "<pre>";
            print_r(array_slice($solicitudes, 0, 2)); // Solo primeras 2 solicitudes  
            echo "</pre>";
        }
        
    } else {
        echo "<h3>❌ No hay autenticación admin</h3>";
        echo "Sesión actual: <pre>" . print_r($_SESSION, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>🌐 Prueba de API Directa:</h2>";
echo "<a href='/admin/api/botiquin?action=dashboard' target='_blank'>Probar Dashboard API</a><br>";
echo "<a href='/admin/api/botiquin?action=instalaciones' target='_blank'>Probar Instalaciones API</a><br>";

echo "<h2>🔗 Enlaces de Navegación:</h2>";
echo "<a href='/admin/dashboard'>Dashboard Admin</a><br>";
echo "<a href='/admin/botiquin'>Gestión Botiquín</a><br>";
echo "<a href='/admin/login'>Login Admin</a><br>";

?> 