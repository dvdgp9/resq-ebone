<?php
// Servicio de Autenticación para Panel Admin de ResQ
// Sistema de login para administradores (superadmin + futuros coordinadores)

require_once __DIR__ . '/AdminPermissionsService.php';

class AdminAuthService {
    
    /**
     * Autentica un administrador usando email y password
     * @param string $email Email del administrador
     * @param string $password Password del administrador
     * @return array|false Datos del administrador o false si falla
     */
    public function login($email, $password) {
        try {
            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                logMessage("Intento de login admin con email inválido: {$email}", 'WARNING');
                return false;
            }
            
            // Buscar administrador en la base de datos
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id, email, password_hash, nombre, tipo, telefono, activo,
                       fecha_creacion, fecha_actualizacion
                FROM admins
                WHERE email = ? AND activo = 1
            ");
            
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin) {
                logMessage("Intento de login admin fallido para email: {$email}", 'WARNING');
                return false;
            }
            
            // Verificar password
            if (!password_verify($password, $admin['password_hash'])) {
                logMessage("Password incorrecto para admin: {$email}", 'WARNING');
                return false;
            }
            
            // Crear sesión admin
            $sessionId = $this->crearSesionAdmin($admin['id']);
            
            if ($sessionId) {
                // Guardar datos en sesión PHP
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_tipo'] = $admin['tipo'];
                $_SESSION['admin_telefono'] = $admin['telefono'];
                $_SESSION['admin_session_id'] = $sessionId;
                $_SESSION['admin_login_time'] = time();
                
                logMessage("Login admin exitoso para {$admin['nombre']} (Email: {$email}) - Tipo: {$admin['tipo']}", 'INFO');
                return $admin;
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error en login admin: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Cierra la sesión del administrador actual
     */
    public function logout() {
        try {
            // Marcar sesión como inactiva en BD
            if (isset($_SESSION['admin_session_id'])) {
                $this->cerrarSesionAdmin($_SESSION['admin_session_id']);
            }
            
            $nombre = $_SESSION['admin_nombre'] ?? 'Administrador desconocido';
            
            // Limpiar sesión PHP (solo datos admin)
            unset($_SESSION['admin_id']);
            unset($_SESSION['admin_email']);
            unset($_SESSION['admin_nombre']);
            unset($_SESSION['admin_tipo']);
            unset($_SESSION['admin_telefono']);
            unset($_SESSION['admin_session_id']);
            unset($_SESSION['admin_login_time']);
            
            logMessage("Logout admin exitoso para {$nombre}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error en logout admin: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verifica si el usuario actual está autenticado como admin
     */
    public function estaAutenticadoAdmin() {
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_session_id'])) {
            return false;
        }
        
        // Verificar que la sesión admin sigue activa en BD
        return $this->verificarSesionAdmin($_SESSION['admin_session_id']);
    }
    
    /**
     * Verifica si el admin actual es superadmin
     */
    public function esSuperAdmin() {
        return $this->estaAutenticadoAdmin() && 
               isset($_SESSION['admin_tipo']) && 
               $_SESSION['admin_tipo'] === 'superadmin';
    }
    
    /**
     * Verifica si el admin actual es admin regular
     */
    public function esAdmin() {
        return $this->estaAutenticadoAdmin() && 
               isset($_SESSION['admin_tipo']) && 
               $_SESSION['admin_tipo'] === 'admin';
    }
    
    /**
     * Verifica si el admin actual es coordinador
     */
    public function esCoordinador() {
        return $this->estaAutenticadoAdmin() && 
               isset($_SESSION['admin_tipo']) && 
               $_SESSION['admin_tipo'] === 'coordinador';
    }
    
    /**
     * Obtiene los datos del administrador actual
     */
    public function getAdminActual() {
        if (!$this->estaAutenticadoAdmin()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'],
            'nombre' => $_SESSION['admin_nombre'],
            'tipo' => $_SESSION['admin_tipo'],
            'telefono' => $_SESSION['admin_telefono'] ?? null,
            'login_time' => $_SESSION['admin_login_time']
        ];
    }
    
    /**
     * Obtiene una instancia de AdminPermissionsService para el admin actual
     */
    public function getPermissionsService() {
        $admin = $this->getAdminActual();
        if (!$admin) {
            return null;
        }
        
        return new AdminPermissionsService($admin);
    }
    
    /**
     * Verificar si el admin actual puede acceder a un coordinador específico
     */
    public function puedeAccederCoordinador($coordinadorId) {
        $permissions = $this->getPermissionsService();
        return $permissions ? $permissions->puedeAccederCoordinador($coordinadorId) : false;
    }
    
    /**
     * Verificar si el admin actual puede acceder a una instalación específica
     */
    public function puedeAccederInstalacion($instalacionId) {
        $permissions = $this->getPermissionsService();
        return $permissions ? $permissions->puedeAccederInstalacion($instalacionId) : false;
    }
    
    /**
     * Verificar si el admin actual puede acceder a un socorrista específico
     */
    public function puedeAccederSocorrista($socorristaId) {
        $permissions = $this->getPermissionsService();
        return $permissions ? $permissions->puedeAccederSocorrista($socorristaId) : false;
    }
    
    /**
     * Obtener descripción del rol del admin actual
     */
    public function getDescripcionRol() {
        if ($this->esSuperAdmin()) {
            return 'Superadministrador';
        } elseif ($this->esAdmin()) {
            return 'Administrador';
        } elseif ($this->esCoordinador()) {
            return 'Coordinador';
        }
        return 'Rol desconocido';
    }
    
    /**
     * Obtener resumen de permisos del admin actual (para debugging)
     */
    public function getResumenPermisos() {
        $permissions = $this->getPermissionsService();
        return $permissions ? $permissions->getResumenPermisos() : null;
    }
    
    /**
     * Crea una nueva sesión admin en la base de datos
     */
    private function crearSesionAdmin($adminId) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Generar ID de sesión único
            $sessionId = generateSecureToken(64);
            
            // Calcular fecha de expiración (4 horas para admin)
            $fechaExpiracion = date('Y-m-d H:i:s', time() + 14400);
            
            $stmt = $db->prepare("
                INSERT INTO admin_sesiones (id, admin_id, fecha_expiracion, activa)
                VALUES (?, ?, ?, 1)
            ");
            
            $stmt->execute([$sessionId, $adminId, $fechaExpiracion]);
            
            return $sessionId;
            
        } catch (Exception $e) {
            logMessage("Error creando sesión admin: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verifica si una sesión admin está activa y no ha expirado
     */
    private function verificarSesionAdmin($sessionId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT id FROM admin_sesiones 
                WHERE id = ? AND activa = 1 AND fecha_expiracion > NOW()
            ");
            
            $stmt->execute([$sessionId]);
            return $stmt->fetch() !== false;
            
        } catch (Exception $e) {
            logMessage("Error verificando sesión admin: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Marca una sesión admin como inactiva
     */
    private function cerrarSesionAdmin($sessionId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE admin_sesiones SET activa = 0 WHERE id = ?
            ");
            
            $stmt->execute([$sessionId]);
            return true;
            
        } catch (Exception $e) {
            logMessage("Error cerrando sesión admin: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Limpia sesiones admin expiradas (mantenimiento)
     */
    public function limpiarSesionesExpiradas() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE admin_sesiones SET activa = 0 
                WHERE fecha_expiracion < NOW() AND activa = 1
            ");
            
            $stmt->execute();
            $sesionesLimpiadas = $stmt->rowCount();
            
            if ($sesionesLimpiadas > 0) {
                logMessage("Limpiadas {$sesionesLimpiadas} sesiones admin expiradas", 'INFO');
            }
            
            return $sesionesLimpiadas;
            
        } catch (Exception $e) {
            logMessage("Error limpiando sesiones admin: " . $e->getMessage(), 'ERROR');
            return 0;
        }
    }
}
?> 