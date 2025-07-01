<?php
// Servicio de Email Simple para ResQ (sin PHPMailer)
// Fallback usando mail() nativo de PHP

class SimpleEmailService {
    
    /**
     * Envía notificación de formulario completado al coordinador
     */
    public function enviarNotificacionFormulario($formularioData) {
        try {
            // Obtener datos del coordinador
            $coordinadorData = $this->obtenerCoordinadorPorSocorrista($formularioData['socorrista_id']);
            
            if (!$coordinadorData) {
                logMessage("No se encontró coordinador para socorrista ID: {$formularioData['socorrista_id']}", 'ERROR');
                return false;
            }
            
            // Configurar email
            $destinatario = $coordinadorData['email'];
            $tipoFormulario = $this->obtenerNombreFormulario($formularioData['tipo_formulario']);
            $asunto = "ResQ - Nuevo {$tipoFormulario} registrado";
            
            // Generar contenido
            $mensaje = $this->generarContenidoTexto($formularioData, $coordinadorData);
            
            // Headers del email
            $headers = [
                'From: ' . SMTP_FROM_EMAIL,
                'Reply-To: ' . SMTP_FROM_EMAIL,
                'X-Mailer: ResQ System',
                'Content-Type: text/plain; charset=UTF-8'
            ];
            
            // Enviar email
            $resultado = mail($destinatario, $asunto, $mensaje, implode("\r\n", $headers));
            
            if ($resultado) {
                logMessage("Email enviado exitosamente a {$destinatario} para formulario ID {$formularioData['id']}", 'INFO');
                return true;
            } else {
                logMessage("Error enviando email a {$destinatario}", 'ERROR');
                return false;
            }
            
        } catch (Exception $e) {
            logMessage("Error en envío de email: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    private function obtenerCoordinadorPorSocorrista($socorristaId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT c.nombre, c.email, i.nombre as instalacion
                FROM coordinadores c
                JOIN instalaciones i ON c.id = i.coordinador_id
                JOIN socorristas s ON i.id = s.instalacion_id
                WHERE s.id = ? AND i.activo = 1 AND s.activo = 1
            ");
            
            $stmt->execute([$socorristaId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logMessage("Error obteniendo coordinador: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }
    
    private function obtenerNombreFormulario($tipo) {
        $nombres = [
            'control_flujo' => 'Control de Flujo de Personas',
            'incidencias' => 'Reporte de Incidencia',
            'solicitud_botiquin' => 'Solicitud de Material para Botiquín'
        ];
        
        return $nombres[$tipo] ?? 'Formulario';
    }
    
    private function generarContenidoTexto($formularioData, $coordinadorData) {
        $tipoFormulario = $this->obtenerNombreFormulario($formularioData['tipo_formulario']);
        $fecha = date('d/m/Y H:i', strtotime($formularioData['fecha_creacion']));
        
        $mensaje = "
ResQ - Notificación de Formulario

Hola {$coordinadorData['nombre']},

Se ha registrado un nuevo {$tipoFormulario} en tu instalación {$coordinadorData['instalacion']}.

Detalles del Formulario:
- Tipo: {$tipoFormulario}
- Fecha y Hora: {$fecha}
- ID del Formulario: #{$formularioData['id']}
- Socorrista: {$formularioData['socorrista_nombre']}";

        // Contenido específico para solicitudes de botiquín
        if ($formularioData['tipo_formulario'] === 'solicitud_botiquin') {
            if (isset($formularioData['elementos_solicitados']) && !empty($formularioData['elementos_solicitados'])) {
                $mensaje .= "\n\nElementos Solicitados:";
                foreach ($formularioData['elementos_solicitados'] as $elemento) {
                    $mensaje .= "\n- {$elemento['nombre']}: {$elemento['cantidad']} unidades";
                    if (!empty($elemento['observaciones'])) {
                        $mensaje .= " ({$elemento['observaciones']})";
                    }
                }
            }
            
            if (isset($formularioData['mensaje_adicional']) && !empty($formularioData['mensaje_adicional'])) {
                $mensaje .= "\n\nMensaje Adicional:\n{$formularioData['mensaje_adicional']}";
            }
        }

        $mensaje .= "\n\nPara revisar los detalles completos, accede al panel de administración:
" . BASE_URL . "/admin

Este es un mensaje automático del sistema ResQ.
        ";
        
        return $mensaje;
    }
    
    /**
     * Método para probar la configuración de email
     */
    public function probarConfiguracion($emailDestino) {
        try {
            $asunto = 'ResQ - Prueba de Configuración de Email (Simple)';
            $mensaje = "
Prueba de Configuración ResQ

Si recibes este email, la configuración básica de email está funcionando.

Fecha: " . date('d/m/Y H:i:s') . "
Método: PHP mail() nativo

Nota: Esta es la versión simple sin PHPMailer.
Para funcionalidad completa, ejecutar: composer install
            ";
            
            $headers = [
                'From: ' . SMTP_FROM_EMAIL,
                'Reply-To: ' . SMTP_FROM_EMAIL,
                'X-Mailer: ResQ System (Simple)',
                'Content-Type: text/plain; charset=UTF-8'
            ];
            
            $resultado = mail($emailDestino, $asunto, $mensaje, implode("\r\n", $headers));
            
            if ($resultado) {
                logMessage("Email de prueba simple enviado a {$emailDestino}", 'INFO');
                return true;
            } else {
                logMessage("Error enviando email de prueba simple", 'ERROR');
                return false;
            }
            
        } catch (Exception $e) {
            logMessage("Error en prueba de email simple: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}
?> 