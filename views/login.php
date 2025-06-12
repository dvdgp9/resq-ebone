<?php
// Vista de Login para ResQ
require_once 'controllers/login.php';

// Mensaje de logout
$logoutMessage = '';
if (isset($_GET['logout'])) {
    $logoutMessage = 'Sesión cerrada correctamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResQ - Acceso Socorristas</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">🚨</div>
        <h1 class="login-title">ResQ</h1>
        <p class="login-subtitle">Sistema de Socorristas</p>
        
        <?php if ($logoutMessage): ?>
            <div class="message message-success"><?= htmlspecialchars($logoutMessage) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label for="dni">DNI</label>
                <input 
                    type="text" 
                    id="dni" 
                    name="dni" 
                    class="form-input form-input-center"
                    placeholder="12345678Z"
                    maxlength="9"
                    required
                    autocomplete="off"
                    value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
                >
                <div class="form-help">Formato: 8 números + letra (ej: 12345678Z)</div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                🔐 Acceder
            </button>
        </form>
        
        <div class="login-help">
            <strong>¿Problemas para acceder?</strong><br>
            Contacta con tu coordinador para verificar que tu DNI esté registrado en el sistema.
        </div>
    </div>
    
    <script>
        // Auto-formatear DNI mientras se escribe
        document.getElementById('dni').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^0-9A-Z]/g, '');
            
            // Limitar a 9 caracteres
            if (value.length > 9) {
                value = value.substring(0, 9);
            }
            
            e.target.value = value;
        });
        
        // Focus automático en el campo DNI
        document.getElementById('dni').focus();
    </script>
</body>
</html> 