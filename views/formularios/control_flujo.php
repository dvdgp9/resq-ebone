<?php
require_once __DIR__ . '/../../classes/AuthService.php';

// Verificar autenticación
$auth = new AuthService();
if (!$auth->estaAutenticado()) {
    header('Location: /login');
    exit;
}

$socorrista = $auth->getSocorristaActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Flujo de Personas - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 Control de Flujo de Personas</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($socorrista['nombre']); ?></span>
                <span>🏢 <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?></span>
                <a href="/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <form id="controlFlujoForm" class="form">
                    <div class="form-group">
                        <label for="fecha_hora">📅 Fecha y Hora *</label>
                        <input type="datetime-local" id="fecha_hora" name="fecha_hora" required>
                        <div class="form-help">Se establecerá automáticamente la fecha/hora actual</div>
                    </div>

                    <div class="form-group">
                        <label for="tipo_movimiento">🚪 Tipo de Movimiento *</label>
                        <select id="tipo_movimiento" name="tipo_movimiento" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="entrada">🔵 Entrada</option>
                            <option value="salida">🔴 Salida</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_personas">👥 Número de Personas *</label>
                        <input type="number" id="numero_personas" name="numero_personas" 
                               min="1" max="1000" required placeholder="Ej: 15">
                        <div class="form-help">Entre 1 y 1000 personas</div>
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">📍 Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" 
                               placeholder="Ej: Entrada principal, Salida de emergencia...">
                        <div class="form-help">Lugar específico donde ocurre el movimiento</div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">📝 Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="4" 
                                  placeholder="Información adicional relevante..."></textarea>
                        <div class="form-help">Detalles adicionales sobre el flujo de personas</div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="window.location.href='/dashboard'" class="btn btn-secondary">
                            ← Volver al Dashboard
                        </button>
                        <button type="submit" class="btn btn-primary">
                            📝 Registrar Control de Flujo
                        </button>
                    </div>
                </form>

                <!-- Mensaje de resultado -->
                <div id="message" class="message" style="display: none;"></div>
            </div>
        </main>
    </div>

    <script>
        // Establecer fecha/hora actual por defecto
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                .toISOString().slice(0, 16);
            document.getElementById('fecha_hora').value = localDateTime;
        });

        // Manejar envío del formulario
        document.getElementById('controlFlujoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('message');
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Enviando...';
            messageDiv.style.display = 'none';
            
            try {
                // Recopilar datos del formulario
                const formData = {
                    fecha_hora: document.getElementById('fecha_hora').value,
                    tipo_movimiento: document.getElementById('tipo_movimiento').value,
                    numero_personas: parseInt(document.getElementById('numero_personas').value),
                    ubicacion: document.getElementById('ubicacion').value,
                    observaciones: document.getElementById('observaciones').value
                };
                
                // Enviar a API
                const response = await fetch('/api/control-flujo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Éxito
                    messageDiv.className = 'message success';
                    messageDiv.textContent = '✅ ' + result.message;
                    messageDiv.style.display = 'block';
                    
                    // Limpiar formulario
                    this.reset();
                    
                    // Restablecer fecha/hora actual
                    const now = new Date();
                    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                        .toISOString().slice(0, 16);
                    document.getElementById('fecha_hora').value = localDateTime;
                    
                    // Scroll al mensaje
                    messageDiv.scrollIntoView({ behavior: 'smooth' });
                    
                } else {
                    // Error
                    messageDiv.className = 'message error';
                    messageDiv.textContent = '❌ ' + (result.error || 'Error al enviar el formulario');
                    messageDiv.style.display = 'block';
                }
                
            } catch (error) {
                messageDiv.className = 'message error';
                messageDiv.textContent = '❌ Error de conexión: ' + error.message;
                messageDiv.style.display = 'block';
            }
            
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.textContent = '📝 Registrar Control de Flujo';
        });
    </script>
</body>
</html> 