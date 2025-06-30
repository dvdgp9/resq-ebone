<?php
require_once __DIR__ . '/../../classes/AuthService.php';
require_once __DIR__ . '/../../classes/AdminService.php';

// Verificar autenticación
$auth = new AuthService();
if (!$auth->estaAutenticado()) {
    header('Location: /login');
    exit;
}

$socorrista = $auth->getSocorristaActual();

// Obtener espacios de la instalación del socorrista
$adminService = new AdminService();
$espacios = $adminService->getEspaciosInstalacion($socorrista['instalacion_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Flujo por Espacios - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 Control de Flujo por Espacios</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($socorrista['nombre']); ?></span>
                <span>🏢 <?php echo htmlspecialchars($socorrista['instalacion_nombre']); ?></span>
                <a href="/dashboard" class="btn btn-outline">← Dashboard</a>
                <a href="/logout" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <form id="controlFlujoForm" class="form">
                    <!-- Información básica -->
                    <div class="form-section">
                        <h3>📅 Información del Registro</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha">📅 Fecha *</label>
                                <input type="date" id="fecha" name="fecha" required>
                                <div class="form-help">Fecha del control de flujo</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="franja_horaria">🕐 Franja Horaria *</label>
                                <select id="franja_horaria" name="franja_horaria" required>
                                    <!-- Opciones se generan dinámicamente -->
                                </select>
                                <div class="form-help">Selecciona la franja de media hora</div>
                            </div>
                        </div>
                    </div>

                    <!-- Control por espacios -->
                    <div class="form-section">
                        <h3>🏢 Control de Aforo por Espacios</h3>
                        
                        <?php if (empty($espacios)): ?>
                            <div class="alert alert-warning">
                                ⚠️ <strong>No hay espacios configurados</strong><br>
                                Contacta con el administrador para configurar los espacios de esta instalación.
                            </div>
                        <?php else: ?>
                            <div id="espacios-container">
                                <?php foreach ($espacios as $index => $espacio): ?>
                                    <div class="espacio-card">
                                        <div class="espacio-header">
                                            <h4>📍 <?php echo htmlspecialchars($espacio); ?></h4>
                                        </div>
                                        <div class="espacio-content">
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="personas_<?php echo $index; ?>">👥 Número de Personas</label>
                                                    <input type="number" 
                                                           id="personas_<?php echo $index; ?>" 
                                                           name="espacios[<?php echo $index; ?>][personas]" 
                                                           min="0" max="9999" 
                                                           placeholder="0"
                                                           data-espacio="<?php echo htmlspecialchars($espacio); ?>">
                                                    <div class="form-help">Personas presentes en este espacio</div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="observaciones_<?php echo $index; ?>">📝 Observaciones</label>
                                                    <input type="text" 
                                                           id="observaciones_<?php echo $index; ?>" 
                                                           name="espacios[<?php echo $index; ?>][observaciones]" 
                                                           placeholder="Detalles específicos..."
                                                           maxlength="200">
                                                    <div class="form-help">Información adicional sobre este espacio</div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="espacios[<?php echo $index; ?>][nombre]" value="<?php echo htmlspecialchars($espacio); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Resumen total -->
                            <div class="total-summary">
                                <div class="total-card">
                                    <span class="total-label">👥 Total de Personas:</span>
                                    <span id="total-personas" class="total-number">0</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Observaciones generales -->
                    <div class="form-section">
                        <div class="form-group">
                            <label for="observaciones_generales">📝 Observaciones Generales</label>
                            <textarea id="observaciones_generales" name="observaciones_generales" rows="3" 
                                      placeholder="Información adicional sobre el control de flujo..."></textarea>
                            <div class="form-help">Detalles generales sobre el control de aforo</div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="window.location.href='/dashboard'" class="btn btn-secondary">
                            ← Volver al Dashboard
                        </button>
                        <?php if (!empty($espacios)): ?>
                            <button type="submit" class="btn btn-primary">
                                📝 Registrar Control de Flujo
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Mensaje de resultado -->
                <div id="message" class="message" style="display: none;"></div>
            </div>
        </main>
    </div>

    <script>
        // Generar franjas horarias de media hora
        function generateTimeSlots() {
            const select = document.getElementById('franja_horaria');
            const slots = [];
            
            for (let hour = 0; hour < 24; hour++) {
                // :00
                const hour00 = hour.toString().padStart(2, '0') + ':00';
                slots.push(hour00);
                
                // :30
                const hour30 = hour.toString().padStart(2, '0') + ':30';
                slots.push(hour30);
            }
            
            slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = slot;
                select.appendChild(option);
            });
        }
        
        // Obtener franja horaria más cercana
        function getClosestHalfHour() {
            const now = new Date();
            const minutes = now.getMinutes();
            let targetHour = now.getHours();
            let targetMinutes = 0;
            
            if (minutes <= 15) {
                targetMinutes = 0;
            } else if (minutes <= 45) {
                targetMinutes = 30;
            } else {
                // Próxima hora
                targetHour = (targetHour + 1) % 24;
                targetMinutes = 0;
            }
            
            return targetHour.toString().padStart(2, '0') + ':' + 
                   targetMinutes.toString().padStart(2, '0');
        }
        
        // Establecer valores por defecto
        function setDefaultValues() {
            // Fecha actual
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];
            document.getElementById('fecha').value = todayStr;
            
            // Franja horaria más cercana
            const closestSlot = getClosestHalfHour();
            document.getElementById('franja_horaria').value = closestSlot;
        }
        
        // Calcular total de personas
        function updateTotal() {
            const personasInputs = document.querySelectorAll('input[name*="[personas]"]');
            let total = 0;
            
            personasInputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += value;
            });
            
            document.getElementById('total-personas').textContent = total;
        }
        
        // Event listeners para actualizar total
        function setupTotalCalculation() {
            const personasInputs = document.querySelectorAll('input[name*="[personas]"]');
            personasInputs.forEach(input => {
                input.addEventListener('input', updateTotal);
            });
        }
        
        // Inicialización al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            generateTimeSlots();
            setDefaultValues();
            setupTotalCalculation();
            updateTotal();
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
                const formData = new FormData(this);
                
                // Construir estructura de datos
                const data = {
                    fecha: formData.get('fecha'),
                    franja_horaria: formData.get('franja_horaria'),
                    observaciones_generales: formData.get('observaciones_generales') || '',
                    espacios: []
                };
                
                // Recopilar datos de espacios
                const espaciosData = {};
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('espacios[')) {
                        const match = key.match(/espacios\[(\d+)\]\[(\w+)\]/);
                        if (match) {
                            const index = match[1];
                            const field = match[2];
                            
                            if (!espaciosData[index]) {
                                espaciosData[index] = {};
                            }
                            espaciosData[index][field] = value;
                        }
                    }
                }
                
                // Convertir a array y filtrar espacios con datos
                Object.values(espaciosData).forEach(espacio => {
                    const personas = parseInt(espacio.personas) || 0;
                    if (personas > 0 || (espacio.observaciones && espacio.observaciones.trim())) {
                        data.espacios.push({
                            nombre: espacio.nombre,
                            personas: personas,
                            observaciones: espacio.observaciones || ''
                        });
                    }
                });
                
                // Validar que hay al menos un espacio con datos
                if (data.espacios.length === 0) {
                    throw new Error('Debe registrar al menos un espacio con personas o observaciones');
                }
                
                // Calcular total
                data.total_personas = data.espacios.reduce((sum, espacio) => sum + espacio.personas, 0);
                
                // Enviar a API
                const response = await fetch('/api/control-flujo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Éxito
                    messageDiv.className = 'message success';
                    messageDiv.textContent = '✅ ' + result.message;
                    messageDiv.style.display = 'block';
                    
                    // Limpiar solo los campos de personas y observaciones
                    const personasInputs = document.querySelectorAll('input[name*="[personas]"]');
                    const observacionesInputs = document.querySelectorAll('input[name*="[observaciones]"], textarea');
                    
                    personasInputs.forEach(input => input.value = '');
                    observacionesInputs.forEach(input => input.value = '');
                    
                    // Actualizar total
                    updateTotal();
                    
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
                messageDiv.textContent = '❌ Error: ' + error.message;
                messageDiv.style.display = 'block';
            }
            
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.textContent = '📝 Registrar Control de Flujo';
        });
    </script>

    <?php include __DIR__ . '/../partials/footer-navigation.php'; ?>
</body>
</html> 