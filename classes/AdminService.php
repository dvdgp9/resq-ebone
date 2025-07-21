<?php
// Servicio de Administración para Panel Admin de ResQ
// Lógica de negocio para CRUD de coordinadores, instalaciones y socorristas

class AdminService {
    
    /**
     * Obtiene coordinadores según permisos del usuario actual
     */
    public function getCoordinadores($admin = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Construir query según tipo de usuario
            if (!$admin) {
                // Sin admin, devolver array vacío por seguridad
                return [];
            }
            
            $whereClause = "";
            $params = [];
            
            if ($admin['tipo'] === 'superadmin') {
                // Superadmin ve todos los coordinadores
                $whereClause = "WHERE a.tipo = 'coordinador'";
            } elseif ($admin['tipo'] === 'admin') {
                // Admin ve solo coordinadores asignados
                $whereClause = "WHERE a.tipo = 'coordinador' AND a.id IN (
                    SELECT ac.coordinador_id 
                    FROM admin_coordinadores ac 
                    WHERE ac.admin_id = ?
                )";
                $params[] = $admin['id'];
            } elseif ($admin['tipo'] === 'coordinador') {
                // Coordinador se ve a sí mismo
                $whereClause = "WHERE a.tipo = 'coordinador' AND a.id = ?";
                $params[] = $admin['id'];
            } else {
                // Tipo desconocido, no ve nada
                return [];
            }
            
            $stmt = $db->prepare("
                SELECT a.id, a.nombre, a.email, a.telefono,
                       a.fecha_creacion, a.fecha_actualizacion,
                       COUNT(i.id) as total_instalaciones
                FROM admins a
                LEFT JOIN instalaciones i ON a.id = i.coordinador_id AND i.activo = 1
                {$whereClause}
                GROUP BY a.id
                ORDER BY a.nombre ASC
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logMessage("Error obteniendo coordinadores: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Crea un nuevo coordinador
     */
    public function crearCoordinador($datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Validar datos requeridos
            $required = ['nombre', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($datos[$field])) {
                    throw new Exception("Campo requerido: $field");
                }
            }
            
            // Validar longitud de contraseña
            if (strlen($datos['password']) < 8) {
                throw new Exception("La contraseña debe tener al menos 8 caracteres");
            }
            
            // Validar email único
            $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$datos['email']]);
            if ($stmt->fetch()) {
                throw new Exception("El email ya está en uso");
            }
            
            // Usar contraseña proporcionada
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                INSERT INTO admins (nombre, email, telefono, password_hash, tipo, activo)
                VALUES (?, ?, ?, ?, 'coordinador', 1)
            ");
            
            $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $datos['telefono'] ?? null,
                $passwordHash
            ]);
            
            $coordinadorId = $db->lastInsertId();
            
            // Si el coordinador fue creado por un admin (no superadmin), 
            // crear automáticamente la relación en admin_coordinadores
            if (isset($datos['created_by_admin_id'])) {
                $stmt = $db->prepare("
                    INSERT INTO admin_coordinadores (admin_id, coordinador_id, activo)
                    VALUES (?, ?, 1)
                ");
                $stmt->execute([$datos['created_by_admin_id'], $coordinadorId]);
                logMessage("Relación admin-coordinador creada: Admin {$datos['created_by_admin_id']} -> Coordinador {$coordinadorId}", 'INFO');
            }
            
            logMessage("Coordinador creado: ID {$coordinadorId}, {$datos['nombre']}", 'INFO');
            
            return $coordinadorId;
            
        } catch (Exception $e) {
            logMessage("Error creando coordinador: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Actualiza un coordinador existente
     */
    public function actualizarCoordinador($id, $datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar que el coordinador existe
            $stmt = $db->prepare("SELECT id FROM admins WHERE id = ? AND tipo = 'coordinador'");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception("Coordinador no encontrado");
            }
            
            // Validar email único (excluyendo el coordinador actual)
            if (!empty($datos['email'])) {
                $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
                $stmt->execute([$datos['email'], $id]);
                if ($stmt->fetch()) {
                    throw new Exception("El email ya está en uso");
                }
            }
            
            // Validar contraseña si se proporciona
            if (!empty($datos['password']) && strlen($datos['password']) < 8) {
                throw new Exception("La contraseña debe tener al menos 8 caracteres");
            }
            
            // Preparar query según si se actualiza contraseña o no
            if (!empty($datos['password'])) {
                $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE admins 
                    SET nombre = ?, email = ?, telefono = ?, password_hash = ?
                    WHERE id = ? AND tipo = 'coordinador'
                ");
                
                $stmt->execute([
                    $datos['nombre'],
                    $datos['email'],
                    $datos['telefono'] ?? null,
                    $passwordHash,
                    $id
                ]);
            } else {
            $stmt = $db->prepare("
                UPDATE admins 
                SET nombre = ?, email = ?, telefono = ?
                WHERE id = ? AND tipo = 'coordinador'
            ");
            
            $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $datos['telefono'] ?? null,
                $id
            ]);
            }
            
            logMessage("Coordinador actualizado: ID {$id}, {$datos['nombre']}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error actualizando coordinador: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Elimina un coordinador (borrado físico si no tiene instalaciones)
     */
    public function eliminarCoordinador($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar que el coordinador existe
            $stmt = $db->prepare("SELECT nombre FROM admins WHERE id = ? AND tipo = 'coordinador'");
            $stmt->execute([$id]);
            $coordinador = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$coordinador) {
                throw new Exception("Coordinador no encontrado");
            }
            
            // Verificar que no tiene instalaciones (doble verificación de seguridad)
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM instalaciones WHERE coordinador_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                throw new Exception("No se puede eliminar un coordinador que tiene instalaciones asignadas");
            }
            
            // Borrado físico
            $stmt = $db->prepare("DELETE FROM admins WHERE id = ? AND tipo = 'coordinador'");
            $stmt->execute([$id]);
            
            logMessage("Coordinador eliminado: ID {$id}, {$coordinador['nombre']}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error eliminando coordinador: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Obtiene las instalaciones de un coordinador específico
     */
    public function getInstalacionesPorCoordinador($coordinadorId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT i.id, i.nombre, i.direccion, i.activo,
                       i.fecha_creacion,
                       COUNT(s.id) as total_socorristas
                FROM instalaciones i
                LEFT JOIN socorristas s ON i.id = s.instalacion_id AND s.activo = 1
                WHERE i.coordinador_id = ?
                GROUP BY i.id
                ORDER BY i.nombre ASC
            ");
            
            $stmt->execute([$coordinadorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logMessage("Error obteniendo instalaciones del coordinador: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Obtiene instalaciones según permisos del usuario actual
     */
    public function getInstalaciones($admin = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Construir query según tipo de usuario
            if (!$admin) {
                // Sin admin, devolver array vacío por seguridad
                return [];
            }
            
            $whereClause = "";
            $params = [];
            
            if ($admin['tipo'] === 'superadmin') {
                // Superadmin ve todas las instalaciones
                $whereClause = "WHERE 1=1";
            } elseif ($admin['tipo'] === 'admin') {
                // Admin ve instalaciones de coordinadores asignados
                $whereClause = "WHERE i.coordinador_id IN (
                    SELECT ac.coordinador_id 
                    FROM admin_coordinadores ac 
                    WHERE ac.admin_id = ?
                )";
                $params[] = $admin['id'];
            } elseif ($admin['tipo'] === 'coordinador') {
                // Coordinador ve solo sus instalaciones
                $whereClause = "WHERE i.coordinador_id = ?";
                $params[] = $admin['id'];
            } else {
                // Tipo desconocido, no ve nada
                return [];
            }
            
            $stmt = $db->prepare("
                SELECT i.id, i.nombre, i.direccion, i.activo,
                       i.fecha_creacion, i.fecha_actualizacion,
                       i.coordinador_id, i.espacios, i.aforo_maximo,
                       a.nombre as coordinador_nombre, a.email as coordinador_email,
                       COUNT(s.id) as total_socorristas
                FROM instalaciones i
                LEFT JOIN admins a ON i.coordinador_id = a.id
                LEFT JOIN socorristas s ON i.id = s.instalacion_id AND s.activo = 1
                {$whereClause}
                GROUP BY i.id
                ORDER BY i.nombre ASC
            ");
            
            $stmt->execute($params);
            $instalaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar JSON de espacios
            foreach ($instalaciones as &$instalacion) {
                if (!empty($instalacion['espacios'])) {
                    $instalacion['espacios'] = json_decode($instalacion['espacios'], true);
                } else {
                    $instalacion['espacios'] = [];
                }
            }
            
            return $instalaciones;
            
        } catch (Exception $e) {
            logMessage("Error obteniendo instalaciones: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Crea una nueva instalación
     */
    public function crearInstalacion($datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Validar datos requeridos
            $required = ['nombre', 'coordinador_id'];
            foreach ($required as $field) {
                if (empty($datos[$field])) {
                    throw new Exception("Campo requerido: $field");
                }
            }
            
            // Verificar que el coordinador existe
            $stmt = $db->prepare("SELECT id FROM admins WHERE id = ? AND tipo = 'coordinador'");
            $stmt->execute([$datos['coordinador_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Coordinador no encontrado");
            }
            
            // Procesar espacios
            $espacios = [];
            if (!empty($datos['espacios']) && is_array($datos['espacios'])) {
                $espacios = array_filter($datos['espacios'], function($espacio) {
                    return !empty(trim($espacio));
                });
            }
            $espaciosJson = !empty($espacios) ? json_encode(array_values($espacios)) : null;
            
            $stmt = $db->prepare("
                INSERT INTO instalaciones (nombre, direccion, coordinador_id, espacios, aforo_maximo, activo)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $datos['nombre'],
                $datos['direccion'] ?? null,
                $datos['coordinador_id'],
                $espaciosJson,
                !empty($datos['aforo_maximo']) ? (int)$datos['aforo_maximo'] : null
            ]);
            
            $instalacionId = $db->lastInsertId();
            logMessage("Instalación creada: ID {$instalacionId}, {$datos['nombre']}", 'INFO');
            
            return $instalacionId;
            
        } catch (Exception $e) {
            logMessage("Error creando instalación: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Actualiza una instalación existente
     */
    public function actualizarInstalacion($id, $datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar que la instalación existe
            $stmt = $db->prepare("SELECT id FROM instalaciones WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception("Instalación no encontrada");
            }
            
            // Verificar coordinador si se especifica
            if (!empty($datos['coordinador_id'])) {
                $stmt = $db->prepare("SELECT id FROM admins WHERE id = ? AND tipo = 'coordinador'");
                $stmt->execute([$datos['coordinador_id']]);
                if (!$stmt->fetch()) {
                    throw new Exception("Coordinador no encontrado");
                }
            }
            
            // Procesar espacios
            $espacios = [];
            if (!empty($datos['espacios']) && is_array($datos['espacios'])) {
                $espacios = array_filter($datos['espacios'], function($espacio) {
                    return !empty(trim($espacio));
                });
            }
            $espaciosJson = !empty($espacios) ? json_encode(array_values($espacios)) : null;
            
            $stmt = $db->prepare("
                UPDATE instalaciones 
                SET nombre = ?, direccion = ?, coordinador_id = ?, espacios = ?, aforo_maximo = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $datos['nombre'],
                $datos['direccion'] ?? null,
                $datos['coordinador_id'],
                $espaciosJson,
                !empty($datos['aforo_maximo']) ? (int)$datos['aforo_maximo'] : null,
                $id
            ]);
            
            logMessage("Instalación actualizada: ID {$id}, {$datos['nombre']}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error actualizando instalación: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Elimina una instalación (borrado físico si no tiene socorristas activos)
     */
    public function eliminarInstalacion($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar que la instalación existe
            $stmt = $db->prepare("SELECT nombre FROM instalaciones WHERE id = ?");
            $stmt->execute([$id]);
            $instalacion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$instalacion) {
                throw new Exception("Instalación no encontrada");
            }

            // Verificar que no tiene socorristas activos
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM socorristas WHERE instalacion_id = ? AND activo = 1");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                throw new Exception("No se puede eliminar una instalación con socorristas activos asignados");
            }

            // Borrado físico
            $stmt = $db->prepare("DELETE FROM instalaciones WHERE id = ?");
            $stmt->execute([$id]);

            logMessage("Instalación eliminada: ID {$id}, {$instalacion['nombre']}", 'INFO');
            return true;
        } catch (Exception $e) {
            logMessage("Error eliminando instalación: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Obtiene socorristas según permisos del usuario actual
     */
    public function getSocorristas($admin = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Construir query según tipo de usuario
            if (!$admin) {
                // Sin admin, devolver array vacío por seguridad
                return [];
            }
            
            $whereClause = "";
            $params = [];
            
            if ($admin['tipo'] === 'superadmin') {
                // Superadmin ve todos los socorristas
                $whereClause = "WHERE 1=1";
            } elseif ($admin['tipo'] === 'admin') {
                // Admin ve socorristas de instalaciones de coordinadores asignados
                $whereClause = "WHERE i.coordinador_id IN (
                    SELECT ac.coordinador_id 
                    FROM admin_coordinadores ac 
                    WHERE ac.admin_id = ?
                )";
                $params[] = $admin['id'];
            } elseif ($admin['tipo'] === 'coordinador') {
                // Coordinador ve solo socorristas de sus instalaciones
                $whereClause = "WHERE i.coordinador_id = ?";
                $params[] = $admin['id'];
            } else {
                // Tipo desconocido, no ve nada
                return [];
            }
            
            $stmt = $db->prepare("
                SELECT s.id, s.dni, s.nombre, s.email, s.telefono, s.activo,
                       s.fecha_creacion, s.fecha_actualizacion,
                       s.instalacion_id,
                       i.nombre as instalacion_nombre,
                       a.nombre as coordinador_nombre
                FROM socorristas s
                LEFT JOIN instalaciones i ON s.instalacion_id = i.id
                LEFT JOIN admins a ON i.coordinador_id = a.id
                {$whereClause}
                ORDER BY s.nombre ASC
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logMessage("Error obteniendo socorristas: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Crea un nuevo socorrista
     */
    public function crearSocorrista($datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Validar datos requeridos
            $required = ['dni', 'nombre', 'instalacion_id'];
            foreach ($required as $field) {
                if (empty($datos[$field])) {
                    throw new Exception("Campo requerido: $field");
                }
            }
            
            // Validar DNI único
            $stmt = $db->prepare("SELECT id FROM socorristas WHERE dni = ?");
            $stmt->execute([$datos['dni']]);
            if ($stmt->fetch()) {
                throw new Exception("El DNI ya está en uso");
            }
            
            // Verificar que la instalación existe
            $stmt = $db->prepare("SELECT id FROM instalaciones WHERE id = ? AND activo = 1");
            $stmt->execute([$datos['instalacion_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Instalación no encontrada o inactiva");
            }
            
            $stmt = $db->prepare("
                INSERT INTO socorristas (dni, nombre, email, telefono, instalacion_id, activo)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            
            $stmt->execute([
                $datos['dni'],
                $datos['nombre'],
                $datos['email'] ?? null,
                $datos['telefono'] ?? null,
                $datos['instalacion_id']
            ]);
            
            $socorristaId = $db->lastInsertId();
            logMessage("Socorrista creado: ID {$socorristaId}, {$datos['nombre']} ({$datos['dni']})", 'INFO');
            
            return $socorristaId;
            
        } catch (Exception $e) {
            logMessage("Error creando socorrista: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Actualiza un socorrista existente
     */
    public function actualizarSocorrista($id, $datos) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar que el socorrista existe
            $stmt = $db->prepare("SELECT id FROM socorristas WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception("Socorrista no encontrado");
            }
            
            // Validar DNI único (excluyendo el socorrista actual)
            if (!empty($datos['dni'])) {
                $stmt = $db->prepare("SELECT id FROM socorristas WHERE dni = ? AND id != ?");
                $stmt->execute([$datos['dni'], $id]);
                if ($stmt->fetch()) {
                    throw new Exception("El DNI ya está en uso");
                }
            }
            
            // Verificar instalación si se especifica
            if (!empty($datos['instalacion_id'])) {
                $stmt = $db->prepare("SELECT id FROM instalaciones WHERE id = ? AND activo = 1");
                $stmt->execute([$datos['instalacion_id']]);
                if (!$stmt->fetch()) {
                    throw new Exception("Instalación no encontrada o inactiva");
                }
            }
            
            $stmt = $db->prepare("
                UPDATE socorristas 
                SET dni = ?, nombre = ?, email = ?, telefono = ?, instalacion_id = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $datos['dni'],
                $datos['nombre'],
                $datos['email'] ?? null,
                $datos['telefono'] ?? null,
                $datos['instalacion_id'],
                $id
            ]);
            
            logMessage("Socorrista actualizado: ID {$id}, {$datos['nombre']}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error actualizando socorrista: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Desactiva un socorrista (soft delete)
     */
    public function desactivarSocorrista($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("UPDATE socorristas SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            logMessage("Socorrista desactivado: ID {$id}", 'INFO');
            return true;
            
        } catch (Exception $e) {
            logMessage("Error desactivando socorrista: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Obtiene los socorristas de una instalación específica
     */
    public function getSocorristasPorInstalacion($instalacionId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, dni, nombre, email, telefono, activo, fecha_creacion FROM socorristas WHERE instalacion_id = ? ORDER BY nombre ASC");
            $stmt->execute([$instalacionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            logMessage('Error obteniendo socorristas de instalación: '.$e->getMessage(),'ERROR');
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas filtradas según permisos del admin
     */
    public function getEstadisticas($admin = null) {
        try {
            // Si no se pasa admin, devolver estadísticas vacías por seguridad
            if (!$admin) {
                return [
                    'coordinadores' => 0,
                    'instalaciones' => 0,
                    'socorristas' => 0
                ];
            }
            
            // Usar AdminPermissionsService para obtener datos filtrados
            require_once __DIR__ . '/AdminPermissionsService.php';
            $permissions = new AdminPermissionsService($admin);
            
            $stats = [];
            
            // Coordinadores que puede ver
            $coordinadores = $permissions->getCoordinadoresPermitidos();
            $stats['coordinadores'] = count($coordinadores);
            
            // Instalaciones que puede ver
            $instalaciones = $permissions->getInstalacionesPermitidas();
            $stats['instalaciones'] = count($instalaciones);
            
            // Socorristas que puede ver
            $socorristas = $permissions->getSocorristasPermitidos();
            $stats['socorristas'] = count($socorristas);
            
            logMessage("Estadísticas filtradas obtenidas para admin {$admin['id']}: " . json_encode($stats), 'INFO');
            return $stats;
            
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas filtradas: " . $e->getMessage(), 'ERROR');
            // Devolver estadísticas por defecto en caso de error
            return [
                'coordinadores' => 0,
                'instalaciones' => 0,
                'socorristas' => 0
            ];
        }
    }
    
    /**
     * Obtiene los espacios de una instalación específica
     */
    public function getEspaciosInstalacion($instalacionId) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT espacios FROM instalaciones WHERE id = ?");
            $stmt->execute([$instalacionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['espacios'])) {
                return json_decode($result['espacios'], true);
            }
            
            return [];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo espacios de instalación: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
}
?> 