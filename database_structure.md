# ResQ - Estructura de Base de Datos

## Diagrama de Relaciones

```
coordinadores (1) ──→ (N) instalaciones (1) ──→ (N) socorristas (1) ──→ (N) formularios
```

## Tablas Principales

### 1. coordinadores
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

### 2. instalaciones
```sql
CREATE TABLE instalaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    coordinador_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id)
);
```

### 3. socorristas
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
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones(id)
);
```

### 4. formularios
```sql
CREATE TABLE formularios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    socorrista_id INT NOT NULL,
    tipo_formulario ENUM('control_flujo', 'incidencias', 'parte_accidente') NOT NULL, -- NOTA: 'parte_accidente' YA NO SE USA (Enero 2025)
    datos_json JSON NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notificacion_enviada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id)
);
```

### 5. sesiones (para autenticación)
```sql
CREATE TABLE sesiones (
    id VARCHAR(128) PRIMARY KEY,
    socorrista_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id)
);
```

## Índices Recomendados

```sql
-- Índices para mejorar rendimiento
CREATE INDEX idx_socorristas_dni ON socorristas(dni);
CREATE INDEX idx_formularios_tipo ON formularios(tipo_formulario);
CREATE INDEX idx_formularios_fecha ON formularios(fecha_creacion);
CREATE INDEX idx_sesiones_expiracion ON sesiones(fecha_expiracion);
```

## Datos de Ejemplo

### Coordinador de ejemplo:
```sql
INSERT INTO coordinadores (nombre, email, telefono) 
VALUES ('Juan Pérez', 'juan.perez@ebone.es', '666123456');
```

### Instalación de ejemplo:
```sql
INSERT INTO instalaciones (nombre, direccion, coordinador_id) 
VALUES ('Sede Central Madrid', 'Calle Principal 123, Madrid', 1);
```

### Socorrista de ejemplo:
```sql
INSERT INTO socorristas (dni, nombre, email, instalacion_id) 
VALUES ('12345678A', 'María García', 'maria.garcia@ebone.es', 1);
```

## Notas de Diseño

- **DNI como identificador único**: Los socorristas se autentican con su DNI
- **JSON para datos de formularios**: Flexibilidad para diferentes campos por tipo
- **Timestamps automáticos**: Auditoría de creación y modificación
- **Campos activo**: Soft delete para mantener histórico
- **Sesiones**: Control de autenticación sin cookies complejas

## Campos Específicos por Formulario (A DEFINIR)

### Control Flujo de Personas Usuarias
- [ ] Campos pendientes de definir

### Incidencias  
- [ ] Campos pendientes de definir

### Parte de Accidente
- [ ] Campos pendientes de definir

---
*Documento actualizable durante el desarrollo* 