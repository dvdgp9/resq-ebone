<?php
// Controlador para formulario de Control de Flujo de Personas
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $required_fields = ['fecha_hora', 'tipo_movimiento', 'numero_personas'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        // Preparar datos para guardar
        $form_data = [
            'fecha_hora' => $input['fecha_hora'],
            'tipo_movimiento' => $input['tipo_movimiento'], // 'entrada' o 'salida'
            'numero_personas' => (int)$input['numero_personas'],
            'observaciones' => $input['observaciones'] ?? '',
            'ubicacion' => $input['ubicacion'] ?? '',
            'responsable' => $socorrista['nombre'],
            'instalacion' => $socorrista['instalacion_nombre']
        ];
        
        // Validar tipo de movimiento
        if (!in_array($form_data['tipo_movimiento'], ['entrada', 'salida'])) {
            throw new Exception("Tipo de movimiento inválido");
        }
        
        // Validar número de personas
        if ($form_data['numero_personas'] < 1 || $form_data['numero_personas'] > 1000) {
            throw new Exception("Número de personas debe estar entre 1 y 1000");
        }
        
        // Guardar en base de datos
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO formularios (socorrista_id, tipo_formulario, datos_json) 
            VALUES (?, 'control_flujo', ?)
        ");
        
        $json_data = json_encode($form_data, JSON_UNESCAPED_UNICODE);
        $stmt->execute([$socorrista['id'], $json_data]);
        
        $form_id = $db->lastInsertId();
        
        // Enviar notificación por email
        try {
            // Obtener email del coordinador
            $stmt = $db->prepare("
                SELECT c.email, c.nombre, i.nombre as instalacion_nombre
                FROM coordinadores c 
                JOIN instalaciones i ON c.id = i.coordinador_id 
                WHERE i.id = ?
            ");
            $stmt->execute([$socorrista['instalacion_id']]);
            $coordinador = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($coordinador) {
                // Cargar servicio de email
                if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                    require_once __DIR__ . '/../classes/EmailService.php';
                    $emailService = new EmailService();
                } else {
                    require_once __DIR__ . '/../classes/SimpleEmailService.php';
                    $emailService = new SimpleEmailService();
                }
                
                // Preparar datos para notificación
                $formularioData = [
                    'id' => $form_id,
                    'socorrista_id' => $socorrista['id'],
                    'tipo_formulario' => 'control_flujo',
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'socorrista_nombre' => $socorrista['nombre']
                ];
                
                $emailService->enviarNotificacionFormulario($formularioData);
                
                // Marcar notificación como enviada
                $stmt = $db->prepare("UPDATE formularios SET notificacion_enviada = TRUE WHERE id = ?");
                $stmt->execute([$form_id]);
            }
        } catch (Exception $e) {
            // Log error pero no fallar el guardado
            error_log("Error enviando email: " . $e->getMessage());
        }
        
        // Respuesta exitosa
        $response = [
            'success' => true,
            'message' => 'Control de flujo registrado correctamente',
            'form_id' => $form_id
        ];
        
        echo json_encode($response);
        exit; // Asegurar que no hay output adicional
        
    } catch (Exception $e) {
        http_response_code(400);
        $error_response = [
            'error' => $e->getMessage()
        ];
        echo json_encode($error_response);
        exit;
    }
    
} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
?> 