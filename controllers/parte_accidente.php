<?php
// Controlador para formulario de Parte de Accidente
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
        $required_fields = ['fecha_hora', 'tipo_accidente', 'descripcion_accidente', 'persona_afectada_nombre'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        // Tipos de accidente válidos
        $tipos_accidente = [
            'caida',
            'golpe',
            'corte',
            'quemadura',
            'intoxicacion',
            'asfixia',
            'electrocucion',
            'fractura',
            'otro'
        ];
        
        // Gravedad de lesiones
        $gravedad_lesiones = ['leve', 'moderada', 'grave', 'muy_grave'];
        
        // Estados de la persona
        $estados_persona = ['consciente', 'inconsciente', 'semiconsciente'];
        
        // Preparar datos para guardar
        $form_data = [
            'fecha_hora' => $input['fecha_hora'],
            'tipo_accidente' => $input['tipo_accidente'],
            'descripcion_accidente' => $input['descripcion_accidente'],
            'ubicacion_accidente' => $input['ubicacion_accidente'] ?? '',
            
            // Datos de la persona afectada
            'persona_afectada_nombre' => $input['persona_afectada_nombre'],
            'persona_afectada_dni' => $input['persona_afectada_dni'] ?? '',
            'persona_afectada_edad' => (int)($input['persona_afectada_edad'] ?? 0),
            'persona_afectada_telefono' => $input['persona_afectada_telefono'] ?? '',
            
            // Información médica
            'lesiones_descripcion' => $input['lesiones_descripcion'] ?? '',
            'gravedad_lesiones' => $input['gravedad_lesiones'] ?? 'leve',
            'estado_persona' => $input['estado_persona'] ?? 'consciente',
            'primeros_auxilios' => $input['primeros_auxilios'] ?? '',
            'requiere_ambulancia' => (bool)($input['requiere_ambulancia'] ?? false),
            'hospital_derivado' => $input['hospital_derivado'] ?? '',
            
            // Testigos
            'testigos' => $input['testigos'] ?? '',
            
            // Información del socorrista
            'responsable' => $socorrista['nombre'],
            'instalacion' => $socorrista['instalacion_nombre'],
            'fecha_reporte' => date('Y-m-d H:i:s')
        ];
        
        // Validaciones específicas
        if (!in_array($form_data['tipo_accidente'], $tipos_accidente)) {
            throw new Exception("Tipo de accidente inválido");
        }
        
        if (!in_array($form_data['gravedad_lesiones'], $gravedad_lesiones)) {
            throw new Exception("Gravedad de lesiones inválida");
        }
        
        if (!in_array($form_data['estado_persona'], $estados_persona)) {
            throw new Exception("Estado de la persona inválido");
        }
        
        // Validar edad si se proporciona
        if ($form_data['persona_afectada_edad'] < 0 || $form_data['persona_afectada_edad'] > 120) {
            throw new Exception("Edad debe estar entre 0 y 120 años");
        }
        
        // Validar longitud de descripción
        if (strlen($form_data['descripcion_accidente']) < 20) {
            throw new Exception("La descripción del accidente debe tener al menos 20 caracteres");
        }
        
        // Validar nombre de la persona afectada
        if (strlen($form_data['persona_afectada_nombre']) < 2) {
            throw new Exception("El nombre de la persona afectada debe tener al menos 2 caracteres");
        }
        
        // Guardar en base de datos
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO formularios (socorrista_id, tipo_formulario, datos_json) 
            VALUES (?, 'parte_accidente', ?)
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
                
                // Emojis según gravedad y tipo
                $gravedad_emoji = [
                    'leve' => '🟢',
                    'moderada' => '🟡',
                    'grave' => '🟠',
                    'muy_grave' => '🔴'
                ];
                
                $tipo_emoji = [
                    'caida' => '⬇️',
                    'golpe' => '💥',
                    'corte' => '🔪',
                    'quemadura' => '🔥',
                    'intoxicacion' => '☠️',
                    'asfixia' => '🫁',
                    'electrocucion' => '⚡',
                    'fractura' => '🦴',
                    'otro' => '📋'
                ];
                
                // Preparar contenido del email
                $subject = "ResQ - PARTE DE ACCIDENTE " . $gravedad_emoji[$form_data['gravedad_lesiones']] . " " . ucfirst($form_data['gravedad_lesiones']);
                
                $body = "
                <h2 style='color: #d32f2f;'>🚨 PARTE DE ACCIDENTE " . $tipo_emoji[$form_data['tipo_accidente']] . "</h2>
                
                <div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #d32f2f;'>
                    <h3>INFORMACIÓN GENERAL</h3>
                    <p><strong>Instalación:</strong> {$form_data['instalacion']}</p>
                    <p><strong>Socorrista Responsable:</strong> {$form_data['responsable']}</p>
                    <p><strong>Fecha/Hora del Accidente:</strong> {$form_data['fecha_hora']}</p>
                    <p><strong>Ubicación:</strong> {$form_data['ubicacion_accidente']}</p>
                </div>
                
                <div style='background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ff9800;'>
                    <h3>DATOS DEL ACCIDENTE</h3>
                    <p><strong>Tipo:</strong> " . str_replace('_', ' ', ucwords($form_data['tipo_accidente'], '_')) . "</p>
                    <p><strong>Descripción:</strong></p>
                    <div style='background: white; padding: 10px; border-radius: 3px; margin: 5px 0;'>
                        {$form_data['descripcion_accidente']}
                    </div>
                </div>
                
                <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4caf50;'>
                    <h3>PERSONA AFECTADA</h3>
                    <p><strong>Nombre:</strong> {$form_data['persona_afectada_nombre']}</p>";
                
                if (!empty($form_data['persona_afectada_dni'])) {
                    $body .= "<p><strong>DNI:</strong> {$form_data['persona_afectada_dni']}</p>";
                }
                
                if ($form_data['persona_afectada_edad'] > 0) {
                    $body .= "<p><strong>Edad:</strong> {$form_data['persona_afectada_edad']} años</p>";
                }
                
                if (!empty($form_data['persona_afectada_telefono'])) {
                    $body .= "<p><strong>Teléfono:</strong> {$form_data['persona_afectada_telefono']}</p>";
                }
                
                $body .= "</div>";
                
                $body .= "
                <div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2196f3;'>
                    <h3>INFORMACIÓN MÉDICA</h3>
                    <p><strong>Gravedad de Lesiones:</strong> " . $gravedad_emoji[$form_data['gravedad_lesiones']] . " " . ucfirst(str_replace('_', ' ', $form_data['gravedad_lesiones'])) . "</p>
                    <p><strong>Estado de la Persona:</strong> " . ucfirst($form_data['estado_persona']) . "</p>";
                
                if (!empty($form_data['lesiones_descripcion'])) {
                    $body .= "
                    <p><strong>Descripción de Lesiones:</strong></p>
                    <div style='background: white; padding: 10px; border-radius: 3px; margin: 5px 0;'>
                        {$form_data['lesiones_descripcion']}
                    </div>";
                }
                
                if (!empty($form_data['primeros_auxilios'])) {
                    $body .= "
                    <p><strong>Primeros Auxilios Aplicados:</strong></p>
                    <div style='background: white; padding: 10px; border-radius: 3px; margin: 5px 0;'>
                        {$form_data['primeros_auxilios']}
                    </div>";
                }
                
                if ($form_data['requiere_ambulancia']) {
                    $body .= "<p><strong>🚑 REQUIRIÓ AMBULANCIA</strong></p>";
                }
                
                if (!empty($form_data['hospital_derivado'])) {
                    $body .= "<p><strong>Hospital de Derivación:</strong> {$form_data['hospital_derivado']}</p>";
                }
                
                $body .= "</div>";
                
                if (!empty($form_data['testigos'])) {
                    $body .= "
                    <div style='background: #f3e5f5; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #9c27b0;'>
                        <h3>TESTIGOS</h3>
                        <div style='background: white; padding: 10px; border-radius: 3px;'>
                            {$form_data['testigos']}
                        </div>
                    </div>";
                }
                
                $body .= "
                <div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; text-align: center;'>
                    <p><strong>⚠️ DOCUMENTO LEGAL - CONSERVAR PARA REGISTROS</strong></p>
                    <p><small>Fecha de Reporte: {$form_data['fecha_reporte']}</small></p>
                    <p><small>Sistema ResQ - Parte de Accidente #{$form_id}</small></p>
                </div>";
                
                // Preparar datos para notificación
                $formularioData = [
                    'id' => $form_id,
                    'socorrista_id' => $socorrista['id'],
                    'tipo_formulario' => 'parte_accidente',
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
            'message' => 'Parte de accidente registrado correctamente',
            'form_id' => $form_id,
            'gravedad' => $form_data['gravedad_lesiones'],
            'requiere_ambulancia' => $form_data['requiere_ambulancia']
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