<?php
// Controlador para formulario de Control de Flujo por Espacios
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
        $required_fields = ['fecha', 'franja_horaria'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        // Validar que hay espacios con datos
        if (empty($input['espacios']) || !is_array($input['espacios'])) {
            throw new Exception("Debe registrar al menos un espacio con datos");
        }
        
        // Validar estructura de espacios
        foreach ($input['espacios'] as $espacio) {
            if (empty($espacio['nombre'])) {
                throw new Exception("Nombre de espacio requerido");
            }
            if (isset($espacio['personas']) && ($espacio['personas'] < 0 || $espacio['personas'] > 9999)) {
                throw new Exception("Número de personas debe estar entre 0 y 9999");
            }
        }
        
        // Preparar datos para guardar (nueva estructura)
        $form_data = [
            'fecha' => $input['fecha'],
            'franja_horaria' => $input['franja_horaria'],
            'fecha_hora' => $input['fecha'] . ' ' . $input['franja_horaria'] . ':00', // Para compatibilidad
            'espacios' => $input['espacios'],
            'total_personas' => $input['total_personas'] ?? 0,
            'observaciones_generales' => $input['observaciones_generales'] ?? '',
            'responsable' => $socorrista['nombre'],
            'instalacion' => $socorrista['instalacion_nombre'],
            'tipo_formulario' => 'control_flujo_v2', // Identificar nueva versión
            
            // Campos para compatibilidad con exportación actual
            'tipo_movimiento' => 'registro_aforo', // Identificador para diferenciar del sistema anterior
            'numero_personas' => $input['total_personas'] ?? 0,
            'ubicacion' => 'Múltiples espacios',
            'observaciones' => $input['observaciones_generales'] ?? ''
        ];
        
        // Validar fecha
        $fecha = DateTime::createFromFormat('Y-m-d', $input['fecha']);
        if (!$fecha) {
            throw new Exception("Formato de fecha inválido");
        }
        
        // Validar franja horaria
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):(00|30)$/', $input['franja_horaria'])) {
            throw new Exception("Franja horaria inválida. Debe ser en formato HH:00 o HH:30");
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
        
        // NOTA: No se envía notificación por email para formularios de control de flujo
        // Solo se envían emails para incidencias y solicitudes de botiquín
        
        // Preparar mensaje de éxito con resumen
        $total_espacios = count($input['espacios']);
        $total_personas = $input['total_personas'] ?? 0;
        
        $mensaje_exito = "Control de flujo registrado correctamente";
        if ($total_espacios > 0) {
            $mensaje_exito .= " - {$total_espacios} espacio" . ($total_espacios > 1 ? 's' : '') . 
                             " registrado" . ($total_espacios > 1 ? 's' : '');
        }
        if ($total_personas > 0) {
            $mensaje_exito .= " - Total: {$total_personas} persona" . ($total_personas > 1 ? 's' : '');
        }
        
        // Respuesta exitosa
        $response = [
            'success' => true,
            'message' => $mensaje_exito,
            'form_id' => $form_id,
            'resumen' => [
                'espacios_registrados' => $total_espacios,
                'total_personas' => $total_personas,
                'fecha' => $input['fecha'],
                'franja_horaria' => $input['franja_horaria']
            ]
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