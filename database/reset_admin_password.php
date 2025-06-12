<?php
// Script para resetear la contraseña del admin
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

try {
    echo "🔧 Reseteando contraseña de admin...\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Generar nuevo hash para admin123
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "📝 Nuevo hash generado: {$hash}\n";
    
    // Actualizar o insertar el admin
    $stmt = $db->prepare("
        INSERT INTO admins (email, password_hash, nombre, tipo, coordinador_id) VALUES 
        ('admin@ebone.es', ?, 'Administrador ResQ', 'superadmin', NULL)
        ON DUPLICATE KEY UPDATE 
        password_hash = ?, nombre = 'Administrador ResQ', tipo = 'superadmin'
    ");
    
    $stmt->execute([$hash, $hash]);
    
    echo "✅ Admin actualizado correctamente\n";
    
    // Verificar el admin creado
    $stmt = $db->prepare("SELECT id, email, nombre, tipo FROM admins WHERE email = 'admin@ebone.es'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin encontrado: {$admin['nombre']} ({$admin['email']}) - Tipo: {$admin['tipo']}\n";
        echo "🔑 Credenciales:\n";
        echo "   Email: admin@ebone.es\n";
        echo "   Password: admin123\n";
    } else {
        echo "❌ No se pudo crear/encontrar el admin\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Reset completado!\n";
?> 