<?php
// Servicio de Email para ResQ
// Maneja el envío de notificaciones a coordinadores

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        try {
            // Configuración del servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            // Configurar SSL para puerto 465
            if (defined('SMTP_SECURITY') && SMTP_SECURITY === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $this->mailer->Port = SMTP_PORT;
            
            // Configuración del remitente
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Configuración de caracteres
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            logMessage("Error configurando SMTP: " . $e->getMessage(), 'ERROR');
            throw new Exception("Error en configuración de email");
        }
    }
    
    /**
     * Envía notificación de formulario completado al coordinador
     */
    public function enviarNotificacionFormulario($formularioData) {
        try {
            // Obtener datos del coordinador
            $coordinadorData = $this->obtenerCoordinadorPorSocorrista($formularioData['socorrista_id']);
            
            if (!$coordinadorData) {
                throw new Exception("No se encontró coordinador para el socorrista");
            }
            
            // Configurar destinatario
            $this->mailer->addAddress($coordinadorData['email'], $coordinadorData['nombre']);
            
            // Configurar asunto según tipo de formulario
            $tipoFormulario = $this->obtenerNombreFormulario($formularioData['tipo_formulario']);
            $this->mailer->Subject = "ResQ - Nuevo {$tipoFormulario} registrado";
            
            // Generar contenido del email
            $contenidoHTML = $this->generarContenidoHTML($formularioData, $coordinadorData);
            $contenidoTexto = $this->generarContenidoTexto($formularioData, $coordinadorData);
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = $contenidoHTML;
            $this->mailer->AltBody = $contenidoTexto;
            
            // Enviar email
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                logMessage("Email enviado exitosamente a {$coordinadorData['email']} para formulario ID {$formularioData['id']}", 'INFO');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error enviando email: " . $e->getMessage(), 'ERROR');
            return false;
        } finally {
            // Limpiar destinatarios para próximo envío
            $this->mailer->clearAddresses();
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
            'parte_accidente' => 'Parte de Accidente'
        ];
        
        return $nombres[$tipo] ?? 'Formulario';
    }
    
    private function generarContenidoHTML($formularioData, $coordinadorData) {
        $tipoFormulario = $this->obtenerNombreFormulario($formularioData['tipo_formulario']);
        $fecha = date('d/m/Y H:i', strtotime($formularioData['fecha_creacion']));
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background-color: #d32f2f; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .info-box { background-color: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #d32f2f; }
                .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🚨 ResQ - Notificación de Formulario</h1>
            </div>
            
            <div class='content'>
                <h2>Hola {$coordinadorData['nombre']},</h2>
                
                <p>Se ha registrado un nuevo <strong>{$tipoFormulario}</strong> en tu instalación <strong>{$coordinadorData['instalacion']}</strong>.</p>
                
                <div class='info-box'>
                    <h3>📋 Detalles del Formulario:</h3>
                    <p><strong>Tipo:</strong> {$tipoFormulario}</p>
                    <p><strong>Fecha y Hora:</strong> {$fecha}</p>
                    <p><strong>ID del Formulario:</strong> #{$formularioData['id']}</p>
                    <p><strong>Socorrista:</strong> {$formularioData['socorrista_nombre']}</p>
                </div>
                
                <p>Para revisar los detalles completos del formulario, accede al panel de administración de ResQ.</p>
                
                <p><a href='" . BASE_URL . "/admin' style='background-color: #d32f2f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Acceder al Panel</a></p>
            </div>
            
            <div class='footer'>
                <p>Este es un mensaje automático del sistema ResQ. No responder a este email.</p>
                <p>© " . date('Y') . " ResQ - Sistema de Gestión de Socorristas</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    private function generarContenidoTexto($formularioData, $coordinadorData) {
        $tipoFormulario = $this->obtenerNombreFormulario($formularioData['tipo_formulario']);
        $fecha = date('d/m/Y H:i', strtotime($formularioData['fecha_creacion']));
        
        return "
ResQ - Notificación de Formulario

Hola {$coordinadorData['nombre']},

Se ha registrado un nuevo {$tipoFormulario} en tu instalación {$coordinadorData['instalacion']}.

Detalles del Formulario:
- Tipo: {$tipoFormulario}
- Fecha y Hora: {$fecha}
- ID del Formulario: #{$formularioData['id']}
- Socorrista: {$formularioData['socorrista_nombre']}

Para revisar los detalles completos, accede al panel de administración:
" . BASE_URL . "/admin

Este es un mensaje automático del sistema ResQ.
        ";
    }
    
    /**
     * Método para probar la configuración de email
     */
    public function probarConfiguracion($emailDestino) {
        try {
            $this->mailer->addAddress($emailDestino);
            $this->mailer->Subject = 'ResQ - Prueba de Configuración de Email';
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = "
                <h2>🔧 Prueba de Configuración ResQ</h2>
                <p>Si recibes este email, la configuración de SMTP está funcionando correctamente.</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><strong>Servidor:</strong> " . SMTP_HOST . "</p>
            ";
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                logMessage("Email de prueba enviado exitosamente a {$emailDestino}", 'INFO');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error en prueba de email: " . $e->getMessage(), 'ERROR');
            return false;
        } finally {
            $this->mailer->clearAddresses();
        }
    }
}
?> 