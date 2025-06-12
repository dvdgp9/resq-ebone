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
    <title>Parte de Accidente - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🚨 Parte de Accidente</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($socorrista['nombre']); ?></span>
                <span>🏢 <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?></span>
                <a href="/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <div class="form-section-header">
                    <h2>📋 Información del Accidente</h2>
                </div>
                
                <form id="parteAccidenteForm" class="form">
                    <!-- SECCIÓN 1: DATOS DEL ACCIDENTE -->
                    <div class="form-section">
                        <h3>🕐 Datos del Accidente</h3>
                        
                        <div class="form-group">
                            <label for="fecha_hora">📅 Fecha y Hora del Accidente *</label>
                            <input type="datetime-local" id="fecha_hora" name="fecha_hora" required>
                            <div class="form-help">Momento exacto en que ocurrió el accidente</div>
                        </div>

                        <div class="form-group">
                            <label for="tipo_accidente">⚡ Tipo de Accidente *</label>
                            <select id="tipo_accidente" name="tipo_accidente" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="caida">⬇️ Caída</option>
                                <option value="golpe">💥 Golpe</option>
                                <option value="corte">🔪 Corte</option>
                                <option value="quemadura">🔥 Quemadura</option>
                                <option value="intoxicacion">☠️ Intoxicación</option>
                                <option value="asfixia">🫁 Asfixia</option>
                                <option value="electrocucion">⚡ Electrocución</option>
                                <option value="fractura">🦴 Fractura</option>
                                <option value="otro">📋 Otro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="descripcion_accidente">📝 Descripción del Accidente *</label>
                            <textarea id="descripcion_accidente" name="descripcion_accidente" rows="4" required
                                      placeholder="Describe detalladamente cómo ocurrió el accidente..."></textarea>
                            <div class="form-help">Mínimo 20 caracteres. Incluye causas y circunstancias</div>
                        </div>

                        <div class="form-group">
                            <label for="ubicacion_accidente">📍 Ubicación del Accidente</label>
                            <input type="text" id="ubicacion_accidente" name="ubicacion_accidente" 
                                   placeholder="Ej: Planta 3, Almacén, Oficina 205...">
                            <div class="form-help">Lugar específico donde ocurrió el accidente</div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: DATOS DE LA PERSONA AFECTADA -->
                    <div class="form-section">
                        <h3>👤 Persona Afectada</h3>
                        
                        <div class="form-group">
                            <label for="persona_afectada_nombre">👤 Nombre Completo *</label>
                            <input type="text" id="persona_afectada_nombre" name="persona_afectada_nombre" required
                                   placeholder="Nombre y apellidos de la persona afectada">
                            <div class="form-help">Nombre completo de la persona que sufrió el accidente</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="persona_afectada_dni">🆔 DNI/NIE</label>
                                <input type="text" id="persona_afectada_dni" name="persona_afectada_dni" 
                                       placeholder="12345678Z">
                                <div class="form-help">Documento de identidad</div>
                            </div>

                            <div class="form-group">
                                <label for="persona_afectada_edad">🎂 Edad</label>
                                <input type="number" id="persona_afectada_edad" name="persona_afectada_edad" 
                                       min="0" max="120" placeholder="25">
                                <div class="form-help">Edad en años</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="persona_afectada_telefono">📞 Teléfono de Contacto</label>
                            <input type="tel" id="persona_afectada_telefono" name="persona_afectada_telefono" 
                                   placeholder="666123456">
                            <div class="form-help">Teléfono para contacto de emergencia</div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: INFORMACIÓN MÉDICA -->
                    <div class="form-section">
                        <h3>🏥 Información Médica</h3>
                        
                        <div class="form-group">
                            <label for="gravedad_lesiones">🎯 Gravedad de las Lesiones</label>
                            <select id="gravedad_lesiones" name="gravedad_lesiones">
                                <option value="leve" selected>🟢 Leve</option>
                                <option value="moderada">🟡 Moderada</option>
                                <option value="grave">🟠 Grave</option>
                                <option value="muy_grave">🔴 Muy Grave</option>
                            </select>
                            <div class="form-help">Evalúa la gravedad de las lesiones</div>
                        </div>

                        <div class="form-group">
                            <label for="estado_persona">🧠 Estado de la Persona</label>
                            <select id="estado_persona" name="estado_persona">
                                <option value="consciente" selected>😊 Consciente</option>
                                <option value="semiconsciente">😵 Semiconsciente</option>
                                <option value="inconsciente">😴 Inconsciente</option>
                            </select>
                            <div class="form-help">Estado de consciencia al momento del reporte</div>
                        </div>

                        <div class="form-group">
                            <label for="lesiones_descripcion">🩹 Descripción de las Lesiones</label>
                            <textarea id="lesiones_descripcion" name="lesiones_descripcion" rows="3"
                                      placeholder="Describe las lesiones visibles y síntomas..."></textarea>
                            <div class="form-help">Detalla las lesiones observadas</div>
                        </div>

                        <div class="form-group">
                            <label for="primeros_auxilios">🚑 Primeros Auxilios Aplicados</label>
                            <textarea id="primeros_auxilios" name="primeros_auxilios" rows="3"
                                      placeholder="Describe los primeros auxilios que se aplicaron..."></textarea>
                            <div class="form-help">Qué medidas de primeros auxilios se tomaron</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="requiere_ambulancia" name="requiere_ambulancia">
                                <span class="checkmark"></span>
                                🚑 Requirió ambulancia
                            </label>
                            <div class="form-help">Marca si fue necesario llamar a servicios de emergencia</div>
                        </div>

                        <div class="form-group">
                            <label for="hospital_derivado">🏥 Hospital de Derivación</label>
                            <input type="text" id="hospital_derivado" name="hospital_derivado" 
                                   placeholder="Nombre del hospital o centro médico">
                            <div class="form-help">Si fue trasladado, indica el centro médico</div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: TESTIGOS -->
                    <div class="form-section">
                        <h3>👥 Testigos</h3>
                        
                        <div class="form-group">
                            <label for="testigos">👁️ Información de Testigos</label>
                            <textarea id="testigos" name="testigos" rows="3"
                                      placeholder="Nombres y datos de contacto de testigos presenciales..."></textarea>
                            <div class="form-help">Personas que presenciaron el accidente</div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="window.location.href='/dashboard'" class="btn btn-secondary">
                            ← Volver al Dashboard
                        </button>
                        <button type="submit" class="btn btn-primary">
                            📋 Registrar Parte de Accidente
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
        document.getElementById('parteAccidenteForm').addEventListener('submit', async function(e) {
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
                    tipo_accidente: document.getElementById('tipo_accidente').value,
                    descripcion_accidente: document.getElementById('descripcion_accidente').value,
                    ubicacion_accidente: document.getElementById('ubicacion_accidente').value,
                    
                    // Datos persona afectada
                    persona_afectada_nombre: document.getElementById('persona_afectada_nombre').value,
                    persona_afectada_dni: document.getElementById('persona_afectada_dni').value,
                    persona_afectada_edad: parseInt(document.getElementById('persona_afectada_edad').value) || 0,
                    persona_afectada_telefono: document.getElementById('persona_afectada_telefono').value,
                    
                    // Información médica
                    gravedad_lesiones: document.getElementById('gravedad_lesiones').value,
                    estado_persona: document.getElementById('estado_persona').value,
                    lesiones_descripcion: document.getElementById('lesiones_descripcion').value,
                    primeros_auxilios: document.getElementById('primeros_auxilios').value,
                    requiere_ambulancia: document.getElementById('requiere_ambulancia').checked,
                    hospital_derivado: document.getElementById('hospital_derivado').value,
                    
                    // Testigos
                    testigos: document.getElementById('testigos').value
                };
                
                // Enviar a API
                const response = await fetch('/api/parte-accidente', {
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
                    
                    // Agregar información sobre la gravedad y ambulancia
                    if (result.gravedad) {
                        const gravedadEmojis = {
                            'leve': '🟢',
                            'moderada': '🟡',
                            'grave': '🟠',
                            'muy_grave': '🔴'
                        };
                        successMsg += ` (Gravedad: ${gravedadEmojis[result.gravedad]} ${result.gravedad.toUpperCase()})`;
                    }
                    
                    if (result.requiere_ambulancia) {
                        successMsg += ' 🚑 AMBULANCIA REQUERIDA';
                    }
                    
                    messageDiv.textContent = successMsg;
                    messageDiv.style.display = 'block';
                    
                    // Limpiar formulario
                    this.reset();
                    
                    // Restablecer valores por defecto
                    const now = new Date();
                    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                        .toISOString().slice(0, 16);
                    document.getElementById('fecha_hora').value = localDateTime;
                    document.getElementById('gravedad_lesiones').value = 'leve';
                    document.getElementById('estado_persona').value = 'consciente';
                    document.getElementById('persona_afectada_edad').value = '';
                    
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
            submitBtn.textContent = '📋 Registrar Parte de Accidente';
        });
    </script>
</body>
</html> 