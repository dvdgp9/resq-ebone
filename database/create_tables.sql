-- Script de creación de tablas para ResQ
-- Base de datos para aplicación de socorristas

-- Tabla de coordinadores
CREATE TABLE IF NOT EXISTS coordinadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de instalaciones
CREATE TABLE IF NOT EXISTS instalaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    coordinador_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE RESTRICT
);

-- Tabla de socorristas
CREATE TABLE IF NOT EXISTS socorristas (
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

-- Tabla de formularios
CREATE TABLE IF NOT EXISTS formularios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    socorrista_id INT NOT NULL,
    tipo_formulario ENUM('control_flujo', 'incidencias', 'parte_accidente') NOT NULL,
    datos_json JSON NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notificacion_enviada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE RESTRICT
);

-- Tabla de sesiones para autenticación
CREATE TABLE IF NOT EXISTS sesiones (
    id VARCHAR(128) PRIMARY KEY,
    socorrista_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (socorrista_id) REFERENCES socorristas(id) ON DELETE CASCADE
);

-- Índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_socorristas_dni ON socorristas(dni);
CREATE INDEX IF NOT EXISTS idx_formularios_tipo ON formularios(tipo_formulario);
CREATE INDEX IF NOT EXISTS idx_formularios_fecha ON formularios(fecha_creacion);
CREATE INDEX IF NOT EXISTS idx_sesiones_expiracion ON sesiones(fecha_expiracion);
CREATE INDEX IF NOT EXISTS idx_instalaciones_coordinador ON instalaciones(coordinador_id);

-- Datos de ejemplo para testing (REMOVER EN PRODUCCIÓN)
INSERT IGNORE INTO coordinadores (id, nombre, email, telefono) VALUES 
(1, 'Juan Pérez Coordinador', 'juan.perez@ebone.es', '666123456');

INSERT IGNORE INTO instalaciones (id, nombre, direccion, coordinador_id) VALUES 
(1, 'Sede Central Madrid', 'Calle Principal 123, 28001 Madrid', 1),
(2, 'Sucursal Barcelona', 'Avenida Diagonal 456, 08008 Barcelona', 1);

INSERT IGNORE INTO socorristas (dni, nombre, email, instalacion_id) VALUES 
('12345678Z', 'María García Socorrista', 'maria.garcia@ebone.es', 1),
('87654321Y', 'Carlos López Rescate', 'carlos.lopez@ebone.es', 2); 