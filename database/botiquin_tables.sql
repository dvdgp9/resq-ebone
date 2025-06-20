-- Script de creación de tablas para Sistema de Botiquín
-- ResQ - Gestión manual por socorristas

-- Inventario de botiquín por instalación (gestión manual completa)
CREATE TABLE IF NOT EXISTS inventario_botiquin (
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

-- Historial de cambios en el inventario
CREATE TABLE IF NOT EXISTS historial_botiquin (
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

-- Solicitudes de material a coordinación
CREATE TABLE IF NOT EXISTS solicitudes_material (
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

-- Índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_inventario_instalacion ON inventario_botiquin(instalacion_id);
CREATE INDEX IF NOT EXISTS idx_inventario_elemento ON inventario_botiquin(nombre_elemento);
CREATE INDEX IF NOT EXISTS idx_historial_inventario ON historial_botiquin(inventario_id);
CREATE INDEX IF NOT EXISTS idx_historial_fecha ON historial_botiquin(fecha_accion);
CREATE INDEX IF NOT EXISTS idx_solicitudes_instalacion ON solicitudes_material(instalacion_id);
CREATE INDEX IF NOT EXISTS idx_solicitudes_estado ON solicitudes_material(estado);

-- Datos de ejemplo para testing (REMOVER EN PRODUCCIÓN)
INSERT IGNORE INTO inventario_botiquin (instalacion_id, nombre_elemento, categoria, cantidad_actual, unidad_medida, observaciones) VALUES 
(1, 'Aspirinas 500mg', 'medicamentos', 25, 'cajas', 'Caja de 20 comprimidos'),
(1, 'Vendas elásticas 5cm', 'material_curacion', 8, 'rollos', 'Vendas de compresión'),
(1, 'Tijeras quirúrgicas', 'instrumental', 2, 'unidades', 'Acero inoxidable'),
(1, 'Gasas estériles', 'material_curacion', 15, 'paquetes', 'Paquetes de 10 unidades'),
(1, 'Termómetro digital', 'instrumental', 1, 'unidades', 'Con funda protectora'),
(2, 'Paracetamol 650mg', 'medicamentos', 30, 'cajas', 'Caja de 20 comprimidos'),
(2, 'Alcohol 70%', 'otros', 3, 'frascos', 'Frasco de 250ml'),
(2, 'Tiritas variadas', 'material_curacion', 50, 'unidades', 'Diferentes tamaños'); 