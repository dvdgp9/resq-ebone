-- Script para agregar 'general' al ENUM de categorías
-- Ejecutar este comando en tu cliente MySQL/PhpMyAdmin

ALTER TABLE inventario_botiquin 
MODIFY categoria ENUM('medicamentos', 'material_curacion', 'instrumental', 'otros', 'general') 
NOT NULL DEFAULT 'general';

-- Verificar que el cambio se aplicó correctamente
DESCRIBE inventario_botiquin; 