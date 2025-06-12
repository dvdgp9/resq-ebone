<?php
// Controlador para formulario de Incidencias
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
        $required_fields = ['descripcion', 'ubicacion', 'acciones_tomadas'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        // Preparar datos para guardar
        $form_data = [
            'nombre_socorrista' => $input['nombre_socorrista'] ?? $socorrista['nombre'],
            'fecha' => $input['fecha'] ?? date('d/m'),
            'instalacion' => $input['instalacion'] ?? $socorrista['instalacion_nombre'],
            'coordinador' => $input['coordinador'] ?? 'Coordinador de ' . $socorrista['instalacion_nombre'],
            'descripcion' => $input['descripcion'],
            'ubicacion' => $input['ubicacion'],
            'acciones_tomadas' => $input['acciones_tomadas'],
            'resuelta' => (bool)($input['resuelta'] ?? false),
            'total_dias' => (int)($input['total_dias'] ?? 1),
            'fecha_completa' => date('Y-m-d H:i:s'),
            'responsable' => $socorrista['nombre'],
            'instalacion_id' => $socorrista['instalacion_id']
        ];
        
        // Validar total de días
        if ($form_data['total_dias'] < 0 || $form_data['total_dias'] > 365) {
            throw new Exception("Total de días debe estar entre 0 y 365");
        }
        
        // Validar longitud de descripción
        if (strlen($form_data['descripcion']) < 10) {
            throw new Exception("La descripción debe tener al menos 10 caracteres");
        }
        
        // Guardar en base de datos
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO formularios (socorrista_id, tipo_formulario, datos_json) 
            VALUES (?, 'incidencias', ?)
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
                
                // Determinar estado de la incidencia
                $estado_emoji = $form_data['resuelta'] ? '✅' : '⏳';
                $estado_texto = $form_data['resuelta'] ? 'RESUELTA' : 'PENDIENTE';
                
                // Preparar contenido del email
                $subject = "ResQ - Nueva Incidencia " . $estado_emoji . " " . $estado_texto;
                $body = "
                <h2>Nueva Incidencia Reportada ⚠️</h2>
                <div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <p><strong>Instalación:</strong> {$form_data['instalacion']}</p>
                    <p><strong>Socorrista:</strong> {$form_data['nombre_socorrista']}</p>
                    <p><strong>Coordinador:</strong> {$form_data['coordinador']}</p>
                    <p><strong>Fecha:</strong> {$form_data['fecha']}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107;'>
                    <p><strong>Estado:</strong> " . $estado_emoji . " " . $estado_texto . "</p>
                    <p><strong>Duración:</strong> {$form_data['total_dias']} día(s)</p>
                    <p><strong>Ubicación:</strong> {$form_data['ubicacion']}</p>
                </div>
                
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <p><strong>Descripción de la incidencia:</strong></p>
                    <p style='margin: 10px 0; padding: 10px; background: white; border-radius: 3px;'>{$form_data['descripcion']}</p>
                </div>
                
                <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745;'>
                    <p><strong>Acciones tomadas:</strong></p>
                    <p style='margin: 10px 0;'>{$form_data['acciones_tomadas']}</p>
                </div>";
                
                $body .= "
                <hr>
                <p><small>Sistema ResQ - Incidencia #{$form_id}</small></p>
                ";
                
                // Preparar datos para notificación
                $formularioData = [
                    'id' => $form_id,
                    'socorrista_id' => $socorrista['id'],
                    'tipo_formulario' => 'incidencias',
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
        echo json_encode([
            'success' => true,
            'message' => 'Incidencia registrada correctamente',
            'form_id' => $form_id,
            'estado' => $form_data['resuelta'] ? 'resuelta' : 'pendiente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
    
} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?> 