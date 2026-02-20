<?php
// Servicio de Autenticación para ResQ
// Sistema basado en usuario y contraseña

class AuthService {
    
    /**
     * Autentica un socorrista usando usuario y contraseña
     * @param string $username Usuario del socorrista
     * @param string $password Contraseña del socorrista
     * @return array|false Datos del socorrista o false si falla
     */
    public function login($username, $password) {
        try {
            // Validar datos
            if (empty($username) || empty($password)) {
                logMessage("Intento de login con credenciales vacías", 'WARNING');
                return false;
            }
            
            // Buscar socorrista en la base de datos
            $db = getDB();
            $stmt = $db->prepare("
                SELECT s.id, s.dni, s.username, s.password_hash, s.nombre, s.email, 
                       i.nombre as instalacion, i.id as instalacion_id,
                       c.nombre as coordinador, c.email as coordinador_email
                FROM socorristas s
                JOIN instalaciones i ON s.instalacion_id = i.id
                JOIN admins c ON i.coordinador_id = c.id
                WHERE s.username = ? AND s.activo = 1 AND i.activo = 1 AND c.tipo = 'coordinador'
            ");
            
            $stmt->execute([$username]);
            $socorrista = $stmt->fetch();
            
            if (!$socorrista) {
                logMessage("Intento de login fallido para usuario: {$username}", 'WARNING');
                return false;
            }
            
            // Verificar contraseña
            if (!password_verify($password, $socorrista['password_hash'])) {
                logMessage("Contraseña incorrecta para usuario: {$username}", 'WARNING');
                return false;
            }
            
            // Crear sesión
            $sessionId = $this->crearSesion($socorrista['id']);
            
            if ($sessionId) {
                // Guardar datos en sesión PHP
                $_SESSION['socorrista_id'] = $socorrista['id'];
                $_SESSION['socorrista_dni'] = $socorrista['dni'];
                $_SESSION['socorrista_username'] = $socorrista['username'];
                $_SESSION['socorrista_nombre'] = $socorrista['nombre'];
                $_SESSION['instalacion_id'] = $socorrista['instalacion_id'];
                $_SESSION['instalacion_nombre'] = $socorrista['instalacion'];
                $_SESSION['session_id'] = $sessionId;
                $_SESSION['login_time'] = time();
                
                logMessage("Login exitoso para {$socorrista['nombre']} (Usuario: {$username})", 'INFO');
                return $socorrista;
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error en login: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Cierra la sesión del usuario actual
     */
    public function logout() {
        try {
            // Marcar sesión como inactiva en BD
            if (isset($_SESSION['session_id'])) {
                $this->cerrarSesion($_SESSION['session_id']);
            }
            
            $nombre = $_SESSION['socorrista_nombre'] ?? 'Usuario desconocido';
            
            // Limpiar sesión PHP
            session_unset();
            session_destroy();
            
            logMessage("Logout exitoso para {$nombre}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error en logout: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verifica si el usuario actual está autenticado
     */
    public function estaAutenticado() {
        if (!isset($_SESSION['socorrista_id']) || !isset($_SESSION['session_id'])) {
            return false;
        }
        
        // Verificar que la sesión sigue activa en BD
        return $this->verificarSesion($_SESSION['session_id']);
    }
    
    /**
     * Obtiene los datos del socorrista actual
     */
    public function getSocorristaActual() {
        if (!$this->estaAutenticado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['socorrista_id'],
            'dni' => $_SESSION['socorrista_dni'],
            'username' => $_SESSION['socorrista_username'] ?? null,
            'nombre' => $_SESSION['socorrista_nombre'],
            'instalacion_id' => $_SESSION['instalacion_id'],
            'instalacion_nombre' => $_SESSION['instalacion_nombre'],
            'login_time' => $_SESSION['login_time']
        ];
    }
    
    /**
     * Crea una nueva sesión en la base de datos
     */
    private function crearSesion($socorristaId) {
        try {
            $db = getDB();
            
            // Generar ID de sesión único
            $sessionId = generateSecureToken(64);
            
            // Calcular fecha de expiración (1 hora)
            $fechaExpiracion = date('Y-m-d H:i:s', time() + 3600);
            
            $stmt = $db->prepare("
                INSERT INTO sesiones (id, socorrista_id, fecha_expiracion, activa)
                VALUES (?, ?, ?, 1)
            ");
            
            $stmt->execute([$sessionId, $socorristaId, $fechaExpiracion]);
            
            return $sessionId;
            
        } catch (Exception $e) {
            logMessage("Error creando sesión: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verifica si una sesión está activa y no ha expirado
     */
    private function verificarSesion($sessionId) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                SELECT id FROM sesiones 
                WHERE id = ? AND activa = 1 AND fecha_expiracion > NOW()
            ");
            
            $stmt->execute([$sessionId]);
            return $stmt->fetch() !== false;
            
        } catch (Exception $e) {
            logMessage("Error verificando sesión: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Marca una sesión como inactiva
     */
    private function cerrarSesion($sessionId) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                UPDATE sesiones SET activa = 0 WHERE id = ?
            ");
            
            $stmt->execute([$sessionId]);
            return true;
            
        } catch (Exception $e) {
            logMessage("Error cerrando sesión: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Limpia sesiones expiradas (mantenimiento)
     */
    public function limpiarSesionesExpiradas() {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                UPDATE sesiones SET activa = 0 
                WHERE fecha_expiracion < NOW() AND activa = 1
            ");
            
            $stmt->execute();
            $sesionesLimpiadas = $stmt->rowCount();
            
            if ($sesionesLimpiadas > 0) {
                logMessage("Limpiadas {$sesionesLimpiadas} sesiones expiradas", 'INFO');
            }
            
            return $sesionesLimpiadas;
            
        } catch (Exception $e) {
            logMessage("Error limpiando sesiones: " . $e->getMessage(), 'ERROR');
            return 0;
        }
    }
}
?> 