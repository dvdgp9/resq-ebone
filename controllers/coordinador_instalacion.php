<?php
// API para obtener el coordinador de la instalación del socorrista actual
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/AuthService.php';

// Limpiar cualquier output previo y configurar headers JSON
ob_clean();
error_reporting(0); // Suprimir warnings para respuesta JSON limpia
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
$auth = new AuthService();
if (!$auth->estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$socorrista = $auth->getSocorristaActual();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Obtener coordinador de la instalación
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT c.nombre as coordinador_nombre
            FROM admins c 
            JOIN instalaciones i ON c.id = i.coordinador_id 
            WHERE i.id = ? AND i.activo = 1 AND c.tipo = 'coordinador'
        ");
        
        $stmt->execute([$socorrista['instalacion_id']]);
        $coordinador = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coordinador) {
            echo json_encode([
                'success' => true,
                'coordinador' => $coordinador['coordinador_nombre']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Coordinador no encontrado',
                'coordinador' => 'Coordinador de ' . $socorrista['instalacion_nombre']
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?> 