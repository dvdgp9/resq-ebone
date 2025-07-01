# ResQ - Estructura de Base de Datos

## Diagrama de Relaciones

```
                            ┌─────────────────┐
                            │     admins      │
                            │   (sistema)     │
                            └─────────┬───────┘
                                      │
                            ┌─────────▼───────┐
                            │ admin_coordina- │
                            │     dores       │
                            │  (intermedia)   │
                            └─────────┬───────┘
                                      │
coordinadores (1) ──────────────────┬─▼─→ (N) instalaciones (1) ──→ (N) socorristas (1) ──→ (N) formularios
      │                             │                    │
      │                             │                    │
      ▼                             │                    ▼
inventario_botiquin ←───────────────┘           historial_botiquin
      │                                                  │
      ▼                                                  │
solicitudes_material ←───────────────────────────────────┘
```

## Sistema de Permisos y Administración

### Roles de Usuario:
1. **Superadmin** - Acceso total (técnico)
2. **Admin** - Acceso a coordinadores asignados y todo lo suyo (operativo)
3. **Coordinador** - Acceso solo a sus instalaciones

## Tablas de Administración

### 1. admins (Sistema administrativo)
```sql
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('superadmin', 'admin', 'coordinador') NOT NULL DEFAULT 'admin',
    coordinador_id INT NULL, -- NULL para superadmin, FK para coordinadores
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE CASCADE
);
```

### 2. admin_coordinadores (Relación muchos a muchos)
```sql
CREATE TABLE admin_coordinadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    coordinador_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_admin_coordinador (admin_id, coordinador_id)
);
```

### 3. admin_sesiones (Sesiones de administración)
```sql
CREATE TABLE admin_sesiones (
    id VARCHAR(128) PRIMARY KEY,
    admin_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
```

## Tablas Principales del Sistema

### 4. coordinadores
```sql
CREATE TABLE coordinadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 5. instalaciones
```sql
CREATE TABLE instalaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    coordinador_id INT NOT NULL,
    espacios JSON, -- Configuración de espacios de la instalación
    aforo_maximo INT DEFAULT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE RESTRICT
);
```

### 6. socorristas
```sql
CREATE TABLE socorristas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    instalacion_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(id) ON DELETE RESTRICT
);
```

### 7. formularios
```sql
CREATE TABLE formularios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    socorrista_id INT NOT NULL,
    tipo_formulario ENUM('control_flujo', 'incidencias', 'parte_accidente') NOT NULL, -- NOTA: 'parte_accidente' YA NO SE USA (Enero 2025)
    datos_json JSON NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notificacion_enviada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE RESTRICT
);
```

### 8. sesiones (para autenticación socorristas)
```sql
CREATE TABLE sesiones (
    id VARCHAR(128) PRIMARY KEY,
    socorrista_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE CASCADE
);
```

## Sistema de Botiquín

### 9. inventario_botiquin
```sql
CREATE TABLE inventario_botiquin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instalacion_id INT NOT NULL,
    nombre_elemento VARCHAR(100) NOT NULL,
    categoria ENUM('medicamentos', 'material_curacion', 'instrumental', 'otros') NOT NULL DEFAULT 'otros',
    cantidad_actual INT NOT NULL DEFAULT 0,
    unidad_medida VARCHAR(20) NOT NULL DEFAULT 'unidades',
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    socorrista_ultima_actualizacion INT,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (socorrista_ultima_actualizacion) REFERENCES socorristas(id) ON DELETE SET NULL
);
```

### 10. historial_botiquin
```sql
CREATE TABLE historial_botiquin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inventario_id INT NOT NULL,
    socorrista_id INT NOT NULL,
    accion ENUM('creado', 'actualizado', 'eliminado') NOT NULL,
    cantidad_anterior INT,
    cantidad_nueva INT,
    observaciones TEXT,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventario_id) REFERENCES inventario_botiquin(id) ON DELETE CASCADE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE RESTRICT
);
```

### 11. solicitudes_material
```sql
CREATE TABLE solicitudes_material (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instalacion_id INT NOT NULL,
    socorrista_id INT NOT NULL,
    elementos_solicitados JSON NOT NULL, -- [{"nombre": "Aspirinas", "cantidad": 50, "observaciones": "Urgente"}]
    mensaje_adicional TEXT,
    estado ENUM('pendiente', 'enviada', 'recibida') NOT NULL DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_envio TIMESTAMP NULL,
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE RESTRICT
);
```

## Índices para Optimización

```sql
-- Índices sistema principal
CREATE INDEX idx_socorristas_dni ON socorristas(dni);
CREATE INDEX idx_formularios_tipo ON formularios(tipo_formulario);
CREATE INDEX idx_formularios_fecha ON formularios(fecha_creacion);
CREATE INDEX idx_sesiones_expiracion ON sesiones(fecha_expiracion);
CREATE INDEX idx_instalaciones_coordinador ON instalaciones(coordinador_id);

-- Índices sistema administrativo
CREATE INDEX idx_admin_coordinadores_admin ON admin_coordinadores(admin_id);
CREATE INDEX idx_admin_coordinadores_coordinador ON admin_coordinadores(coordinador_id);
CREATE INDEX idx_admin_sesiones_expiracion ON admin_sesiones(fecha_expiracion);

-- Índices sistema botiquín
CREATE INDEX idx_inventario_instalacion ON inventario_botiquin(instalacion_id);
CREATE INDEX idx_inventario_elemento ON inventario_botiquin(nombre_elemento);
CREATE INDEX idx_historial_inventario ON historial_botiquin(inventario_id);
CREATE INDEX idx_historial_fecha ON historial_botiquin(fecha_accion);
CREATE INDEX idx_solicitudes_instalacion ON solicitudes_material(instalacion_id);
CREATE INDEX idx_solicitudes_estado ON solicitudes_material(estado);
```

## Sistema de Permisos

### Lógica de Acceso:

1. **Superadmin** (`tipo='superadmin'`, `coordinador_id=NULL`):
   - Acceso total a toda la aplicación
   - Puede gestionar admins, coordinadores, instalaciones, socorristas
   - Acceso completo al sistema de botiquín

2. **Admin** (`tipo='admin'`):
   - Acceso a coordinadores asignados en `admin_coordinadores`
   - Acceso a todas las instalaciones de esos coordinadores
   - Acceso a todos los socorristas de esas instalaciones
   - Gestión de botiquín de instalaciones permitidas

3. **Coordinador** (`tipo='coordinador'`):
   - Acceso directo vía login al sistema de socorristas
   - Solo puede gestionar sus instalaciones asignadas
   - Gestión de botiquín solo de sus instalaciones

## Datos de Ejemplo

### Admin Superadmin:
```sql
INSERT INTO admins (email, password_hash, nombre, tipo, coordinador_id) 
VALUES ('admin@ebone.es', '$2y$10$hash...', 'Administrador ResQ', 'superadmin', NULL);
```

### Coordinador:
```sql
INSERT INTO coordinadores (nombre, email, telefono) 
VALUES ('Juan Pérez Coordinador', 'juan.perez@ebone.es', '666123456');
```

### Asignación Admin-Coordinador:
```sql
INSERT INTO admin_coordinadores (admin_id, coordinador_id) 
VALUES (2, 1); -- Admin ID 2 gestiona Coordinador ID 1
```

## Notas de Diseño

- **Sistema de permisos multinivel**: Superadmin → Admin → Coordinador
- **Tabla intermedia**: Permite que un admin gestione múltiples coordinadores
- **Soft delete**: Campos `activo` para mantener histórico
- **Auditoría completa**: Timestamps y historial de cambios
- **JSON flexible**: Para configuraciones complejas (espacios, solicitudes)
- **Índices optimizados**: Para consultas eficientes por permisos

---
*Actualizado: Enero 2025 - Sistema de permisos administrativos implementado* 