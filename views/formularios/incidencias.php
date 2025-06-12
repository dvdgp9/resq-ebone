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
    <title>Reporte de Incidencias - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>⚠️ Reporte de Incidencias</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($socorrista['nombre']); ?></span>
                <span>🏢 <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?></span>
                <a href="/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <form id="incidenciasForm" class="form">
                    <!-- Información del Socorrista (Autorrellenada) -->
                    <div class="form-section">
                        <h3>👤 Información del Socorrista</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_socorrista">Nombre Socorrista</label>
                                <input type="text" id="nombre_socorrista" name="nombre_socorrista" 
                                       value="<?php echo htmlspecialchars($socorrista['nombre']); ?>" readonly>
                                <div class="form-help">Autorrellenado desde tu perfil</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha">📅 Fecha</label>
                                <input type="text" id="fecha" name="fecha" readonly>
                                <div class="form-help">Día y mes actual</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="instalacion">🏢 Instalación</label>
                                <input type="text" id="instalacion" name="instalacion" 
                                       value="<?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?>" readonly>
                                <div class="form-help">Tu instalación asignada</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="coordinador">👨‍💼 Coordinador/a</label>
                                <input type="text" id="coordinador" name="coordinador" readonly>
                                <div class="form-help">Coordinador de tu instalación</div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Incidencia -->
                    <div class="form-section">
                        <h3>⚠️ Detalles de la Incidencia</h3>
                        
                        <div class="form-group">
                            <label for="descripcion">📝 Descripción de la incidencia *</label>
                            <textarea id="descripcion" name="descripcion" rows="4" required
                                      placeholder="Describe detalladamente qué ocurrió..."></textarea>
                            <div class="form-help">Explica claramente la incidencia ocurrida</div>
                        </div>

                        <div class="form-group">
                            <label for="ubicacion">📍 Ubicación *</label>
                            <input type="text" id="ubicacion" name="ubicacion" required
                                   placeholder="Ej: Planta 2, Sala de máquinas, Entrada principal...">
                            <div class="form-help">Lugar específico donde ocurrió la incidencia</div>
                        </div>

                        <div class="form-group">
                            <label for="acciones_tomadas">🛠️ Acciones tomadas *</label>
                            <textarea id="acciones_tomadas" name="acciones_tomadas" rows="3" required
                                      placeholder="Describe las medidas que se tomaron inmediatamente..."></textarea>
                            <div class="form-help">Qué se hizo para resolver o mitigar la incidencia</div>
                        </div>
                    </div>

                    <!-- Estado y Seguimiento -->
                    <div class="form-section">
                        <h3>✅ Estado y Seguimiento</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="resuelta" name="resuelta">
                                    <span class="checkmark"></span>
                                    ¿Resuelta?
                                </label>
                                <div class="form-help">Marca si la incidencia ya está completamente resuelta</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="total_dias">📊 Total días</label>
                                <input type="number" id="total_dias" name="total_dias" 
                                       min="0" max="365" value="1" placeholder="1">
                                <div class="form-help">Número de días que ha durado la incidencia</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="window.location.href='/dashboard'" class="btn btn-secondary">
                            ← Volver al Dashboard
                        </button>
                        <button type="submit" class="btn btn-primary">
                            📤 Enviar Reporte de Incidencia
                        </button>
                    </div>
                </form>

                <!-- Mensaje de resultado -->
                <div id="message" class="message" style="display: none;"></div>
            </div>
        </main>
    </div>

    <script>
        // Establecer fecha actual y obtener coordinador
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer fecha actual (día y mes)
            const now = new Date();
            const fechaFormateada = now.toLocaleDateString('es-ES', { 
                day: '2-digit', 
                month: '2-digit' 
            });
            document.getElementById('fecha').value = fechaFormateada;
            
            // Obtener coordinador real desde la base de datos
            obtenerCoordinador();
        });
        
        // Función para obtener el coordinador de la instalación
        async function obtenerCoordinador() {
            try {
                const response = await fetch('/api/coordinador-instalacion', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.coordinador) {
                        document.getElementById('coordinador').value = result.coordinador;
                    } else {
                        // Fallback si no se encuentra
                        document.getElementById('coordinador').value = 'Coordinador de <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?>';
                    }
                } else {
                    // Fallback en caso de error
                    document.getElementById('coordinador').value = 'Coordinador de <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?>';
                }
            } catch (error) {
                console.error('Error obteniendo coordinador:', error);
                // Fallback en caso de error
                document.getElementById('coordinador').value = 'Coordinador de <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?>';
            }
        }

        // Manejar envío del formulario
        document.getElementById('incidenciasForm').addEventListener('submit', async function(e) {
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
                    nombre_socorrista: document.getElementById('nombre_socorrista').value,
                    fecha: document.getElementById('fecha').value,
                    instalacion: document.getElementById('instalacion').value,
                    coordinador: document.getElementById('coordinador').value,
                    descripcion: document.getElementById('descripcion').value,
                    ubicacion: document.getElementById('ubicacion').value,
                    acciones_tomadas: document.getElementById('acciones_tomadas').value,
                    resuelta: document.getElementById('resuelta').checked,
                    total_dias: parseInt(document.getElementById('total_dias').value) || 1
                };
                
                // Enviar a API
                const response = await fetch('/api/incidencias', {
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
                    let successMsg = '✅ ' + result.message;
                    
                    // Agregar información sobre el estado
                    if (formData.resuelta) {
                        successMsg += ' (Estado: ✅ RESUELTA)';
                    } else {
                        successMsg += ' (Estado: ⏳ PENDIENTE)';
                    }
                    
                    messageDiv.textContent = successMsg;
                    messageDiv.style.display = 'block';
                    
                    // Limpiar solo los campos editables
                    document.getElementById('descripcion').value = '';
                    document.getElementById('ubicacion').value = '';
                    document.getElementById('acciones_tomadas').value = '';
                    document.getElementById('resuelta').checked = false;
                    document.getElementById('total_dias').value = '1';
                    
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
            submitBtn.textContent = '📤 Enviar Reporte de Incidencia';
        });
    </script>
</body>
</html> 