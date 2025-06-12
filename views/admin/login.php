<?php
// Vista de Login Admin para ResQ
require_once __DIR__ . '/../../controllers/admin/auth.php';

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
    <title>Panel Admin - ResQ</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">🛡️</div>
        <h1 class="login-title">Panel Admin</h1>
        <p class="login-subtitle">Sistema de Administración ResQ</p>
        
        <?php if ($logoutMessage): ?>
            <div class="message message-success"><?= htmlspecialchars($logoutMessage) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input form-input-center"
                    placeholder="admin@ebone.es"
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input form-input-center"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                🛡️ Acceder al Panel
            </button>
        </form>
        
        <div class="login-footer">
            <p>🚨 <a href="/" class="link">Volver al login de socorristas</a></p>
            <p><small>Panel de administración ResQ v1.0</small></p>
        </div>
    </div>
    
    <script>
        // Auto-focus en el campo email
        document.getElementById('email').focus();
        
        // Prevenir doble submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '🔄 Verificando...';
            
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '🛡️ Acceder al Panel';
            }, 5000);
        });
    </script>
</body>
</html> 