-- Script de creación de tablas para Panel de Administración ResQ
-- Tablas para gestión de administradores y sesiones admin

-- Tabla de administradores (superadmin + futuros coordinadores)
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('superadmin', 'coordinador') DEFAULT 'coordinador',
    coordinador_id INT NULL, -- NULL para superadmin, FK para coordinadores
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE CASCADE
);

-- Tabla de sesiones admin (separada de sesiones de socorristas)
CREATE TABLE IF NOT EXISTS admin_sesiones (
    id VARCHAR(128) PRIMARY KEY,
    admin_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_admin_email ON admins(email);
CREATE INDEX IF NOT EXISTS idx_admin_tipo ON admins(tipo);
CREATE INDEX IF NOT EXISTS idx_admin_sesiones_expiracion ON admin_sesiones(fecha_expiracion);
CREATE INDEX IF NOT EXISTS idx_admin_sesiones_admin ON admin_sesiones(admin_id);

-- Insertar usuario superadmin inicial  
-- Password: admin123 (cambiar después del primer login)
-- Usar un hash específico conocido que funcione
INSERT IGNORE INTO admins (email, password_hash, nombre, tipo, coordinador_id) VALUES 
('admin@ebone.es', '$2y$10$wR7QoF8U2Y6fQ5z.qO4Rau7X3aJ1vV2Nj8Y5cZ9L0oE3tU4dG6kQ1', 'Administrador ResQ', 'superadmin', NULL);

-- Si el anterior no funciona, crear uno nuevo:
-- UPDATE admins SET password_hash = ? WHERE email = 'admin@ebone.es';

-- Comentario: Password por defecto es 'admin123'
-- Generar nuevo hash con: password_hash('nueva_password', PASSWORD_DEFAULT) 