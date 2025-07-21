<?php
// AdminPermissionsService - Sistema de permisos para panel administrativo
require_once __DIR__ . '/../config/database.php';

class AdminPermissionsService {
    private $db;
    private $admin;
    
    public function __construct($admin) {
        $this->db = Database::getInstance()->getConnection();
        $this->admin = $admin;
    }
    
    /**
     * Verificar si el admin es superadmin
     */
    public function isSuperAdmin() {
        return $this->admin['tipo'] === 'superadmin';
    }
    
    /**
     * Verificar si el admin es admin regular
     */
    public function isAdmin() {
        return $this->admin['tipo'] === 'admin';
    }
    
    /**
     * Verificar si el admin es coordinador
     */
    public function isCoordinador() {
        return $this->admin['tipo'] === 'coordinador';
    }
    
    /**
     * Obtener todos los coordinadores que puede ver este admin
     */
    public function getCoordinadoresPermitidos() {
        if ($this->isSuperAdmin()) {
            // Superadmin puede ver todos los coordinadores
            $stmt = $this->db->prepare("
                SELECT * FROM admins 
                WHERE tipo = 'coordinador'
                ORDER BY nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($this->isAdmin()) {
            // Admin puede ver solo coordinadores asignados
            $stmt = $this->db->prepare("
                SELECT a.* FROM admins a
                INNER JOIN admin_coordinadores ac ON a.id = ac.coordinador_id
                WHERE ac.admin_id = ? AND ac.activo = 1 AND a.tipo = 'coordinador'
                ORDER BY a.nombre
            ");
            $stmt->execute([$this->admin['id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($this->isCoordinador()) {
            // Coordinadores NO pueden ver coordinadores - solo instalaciones y socorristas
            return [];
        }
        
        return [];
    }
    
    /**
     * Obtener IDs de coordinadores permitidos (para consultas)
     */
    public function getCoordinadoresPermitidosIds() {
        $coordinadores = $this->getCoordinadoresPermitidos();
        return array_column($coordinadores, 'id');
    }
    
    /**
     * Obtener todas las instalaciones que puede ver este admin
     */
    public function getInstalacionesPermitidas() {
        if ($this->isSuperAdmin()) {
            // Superadmin puede ver todas las instalaciones
            $stmt = $this->db->prepare("
                SELECT i.*, a.nombre as coordinador_nombre 
                FROM instalaciones i
                INNER JOIN admins a ON i.coordinador_id = a.id
                WHERE i.activo = 1 AND a.tipo = 'coordinador'
                ORDER BY i.nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($this->isCoordinador()) {
            // Coordinador puede ver solo sus propias instalaciones
            $stmt = $this->db->prepare("
                SELECT i.*, a.nombre as coordinador_nombre 
                FROM instalaciones i
                INNER JOIN admins a ON i.coordinador_id = a.id
                WHERE i.coordinador_id = ? AND i.activo = 1 AND a.tipo = 'coordinador'
                ORDER BY i.nombre
            ");
            $stmt->execute([$this->admin['id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $coordinadorIds = $this->getCoordinadoresPermitidosIds();
        if (empty($coordinadorIds)) {
            return [];
        }
        
        // Admin puede ver instalaciones de coordinadores permitidos
        $placeholders = str_repeat('?,', count($coordinadorIds) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT i.*, a.nombre as coordinador_nombre 
            FROM instalaciones i
            INNER JOIN admins a ON i.coordinador_id = a.id
            WHERE i.coordinador_id IN ($placeholders) AND i.activo = 1 AND a.tipo = 'coordinador'
            ORDER BY i.nombre
        ");
        $stmt->execute($coordinadorIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener IDs de instalaciones permitidas (para consultas)
     */
    public function getInstalacionesPermitidasIds() {
        $instalaciones = $this->getInstalacionesPermitidas();
        return array_column($instalaciones, 'id');
    }
    
    /**
     * Obtener todos los socorristas que puede ver este admin
     */
    public function getSocorristasPermitidos() {
        $instalacionIds = $this->getInstalacionesPermitidasIds();
        if (empty($instalacionIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($instalacionIds) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT s.*, i.nombre as instalacion_nombre, a.nombre as coordinador_nombre
            FROM socorristas s
            INNER JOIN instalaciones i ON s.instalacion_id = i.id
            INNER JOIN admins a ON i.coordinador_id = a.id
            WHERE s.instalacion_id IN ($placeholders) AND s.activo = 1 AND a.tipo = 'coordinador'
            ORDER BY s.nombre
        ");
        $stmt->execute($instalacionIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si puede acceder a un coordinador específico
     */
    public function puedeAccederCoordinador($coordinadorId) {
        $coordinadorIds = $this->getCoordinadoresPermitidosIds();
        return in_array($coordinadorId, $coordinadorIds);
    }
    
    /**
     * Verificar si puede acceder a una instalación específica
     */
    public function puedeAccederInstalacion($instalacionId) {
        $instalacionIds = $this->getInstalacionesPermitidasIds();
        return in_array($instalacionId, $instalacionIds);
    }
    
    /**
     * Verificar si puede acceder a un socorrista específico
     */
    public function puedeAccederSocorrista($socorristaId) {
        $socorristas = $this->getSocorristasPermitidos();
        $socorristaIds = array_column($socorristas, 'id');
        return in_array($socorristaId, $socorristaIds);
    }
    
    /**
     * Obtener inventario de botiquín permitido
     */
    public function getInventarioBotiquinPermitido($instalacionId = null) {
        $instalacionIds = $this->getInstalacionesPermitidasIds();
        
        if (empty($instalacionIds)) {
            return [];
        }
        
        // Si se especifica instalación, verificar permisos
        if ($instalacionId && !$this->puedeAccederInstalacion($instalacionId)) {
            return [];
        }
        
        $whereClause = $instalacionId ? 
            "WHERE ib.instalacion_id = ? AND ib.activo = 1" :
            "WHERE ib.instalacion_id IN (" . str_repeat('?,', count($instalacionIds) - 1) . "?) AND ib.activo = 1";
            
        $params = $instalacionId ? [$instalacionId] : $instalacionIds;
        
        $stmt = $this->db->prepare("
            SELECT ib.*, i.nombre as instalacion_nombre, a.nombre as coordinador_nombre,
                   s.nombre as ultima_actualizacion_por
            FROM inventario_botiquin ib
            INNER JOIN instalaciones i ON ib.instalacion_id = i.id
            INNER JOIN admins a ON i.coordinador_id = a.id
            LEFT JOIN socorristas s ON ib.socorrista_ultima_actualizacion = s.id
            $whereClause AND a.tipo = 'coordinador'
            ORDER BY i.nombre, ib.categoria, ib.nombre_elemento
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener solicitudes de material permitidas
     */
    public function getSolicitudesMaterialPermitidas($instalacionId = null) {
        $instalacionIds = $this->getInstalacionesPermitidasIds();
        
        if (empty($instalacionIds)) {
            return [];
        }
        
        // Si se especifica instalación, verificar permisos
        if ($instalacionId && !$this->puedeAccederInstalacion($instalacionId)) {
            return [];
        }
        
        $whereClause = $instalacionId ? 
            "WHERE sm.instalacion_id = ?" :
            "WHERE sm.instalacion_id IN (" . str_repeat('?,', count($instalacionIds) - 1) . "?)";
            
        $params = $instalacionId ? [$instalacionId] : $instalacionIds;
        
        $stmt = $this->db->prepare("
            SELECT sm.*, i.nombre as instalacion_nombre, a.nombre as coordinador_nombre,
                   s.nombre as socorrista_nombre
            FROM solicitudes_material sm
            INNER JOIN instalaciones i ON sm.instalacion_id = i.id
            INNER JOIN admins a ON i.coordinador_id = a.id
            INNER JOIN socorristas s ON sm.socorrista_id = s.id
            $whereClause AND a.tipo = 'coordinador'
            ORDER BY sm.fecha_solicitud DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener resumen de permisos para debugging
     */
    public function getResumenPermisos() {
        return [
            'admin_info' => [
                'id' => $this->admin['id'],
                'nombre' => $this->admin['nombre'],
                'tipo' => $this->admin['tipo']
            ],
            'roles' => [
                'is_superadmin' => $this->isSuperAdmin(),
                'is_admin' => $this->isAdmin(),
                'is_coordinador' => $this->isCoordinador()
            ],
            'permisos' => [
                'coordinadores_count' => count($this->getCoordinadoresPermitidos()),
                'instalaciones_count' => count($this->getInstalacionesPermitidas()),
                'socorristas_count' => count($this->getSocorristasPermitidos())
            ]
        ];
    }
}
?> 