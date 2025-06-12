-- Script para eliminar el campo 'activo' de la tabla coordinadores
-- Ejecutar después de confirmar que no hay coordinadores inactivos importantes

-- Verificar coordinadores inactivos antes de eliminar el campo
SELECT id, nombre, email, activo FROM coordinadores WHERE activo = 0;

-- Si hay coordinadores inactivos que quieres conservar, primero actívalos:
-- UPDATE coordinadores SET activo = 1 WHERE id IN (1,2,3); -- IDs específicos

-- Eliminar el campo activo
ALTER TABLE coordinadores DROP COLUMN activo;

-- Verificar que el campo se eliminó correctamente
DESCRIBE coordinadores; 