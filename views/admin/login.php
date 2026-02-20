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
        <div class="login-logo">
            <img src="/assets/images/logo.png" alt="ResQ Logo" class="logo-image">
        </div>
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
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input form-input-center"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()" title="Mostrar/ocultar contraseña">
                        <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="icon-eye-off" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="form-group remember-me-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me" value="1" id="remember_me">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-text">Recordar sesión (60 días)</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large" style="width: 100%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                Acceder al Panel
            </button>
        </form>
        
        <div class="login-footer">
            <p><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="4.93" x2="9.17" y1="4.93" y2="9.17"/><line x1="14.83" x2="19.07" y1="14.83" y2="19.07"/><line x1="14.83" x2="19.07" y1="9.17" y2="4.93"/><line x1="14.83" x2="18.36" y1="9.17" y2="5.64"/><line x1="4.93" x2="9.17" y1="19.07" y2="14.83"/></svg> <a href="/" class="link">Volver al login de socorristas</a></p>
            <p><small>Panel de administración ResQ v1.0</small></p>
        </div>
    </div>
    
    <style>
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-input-container input {
            padding-right: 45px;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            color: #666;
            border-radius: 3px;
            transition: background-color 0.2s;
        }
        
        .password-toggle:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .password-toggle:focus {
            outline: 2px solid #007bff;
            outline-offset: 1px;
        }
    </style>
    
    <script>
        // Auto-focus en el campo email
        document.getElementById('email').focus();
        
        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.querySelector('.icon-eye').style.display = 'none';
                toggleButton.querySelector('.icon-eye-off').style.display = 'inline';
                toggleButton.title = 'Ocultar contraseña';
            } else {
                passwordField.type = 'password';
                toggleButton.querySelector('.icon-eye').style.display = 'inline';
                toggleButton.querySelector('.icon-eye-off').style.display = 'none';
                toggleButton.title = 'Mostrar contraseña';
            }
        }
        
        // Prevenir doble submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px; animation: spin 1s linear infinite;"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg> Verificando...';
            
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '🛡️ Acceder al Panel';
            }, 5000);
        });
        

    </script>
</body>
</html> 