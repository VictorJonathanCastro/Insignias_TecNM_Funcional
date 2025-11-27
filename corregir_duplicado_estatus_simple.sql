-- ========================================
-- CORRECCIÓN RÁPIDA: Eliminar duplicado "Aprobado" en estatus
-- ========================================
-- Script simplificado para corregir el duplicado
-- ========================================

USE insignia;

-- Paso 1: Ver los duplicados
SELECT id, Nombre_Estatus, Acron_Estatus 
FROM estatus 
WHERE Nombre_Estatus = 'Aprobado' 
ORDER BY id;

-- Paso 2: Actualizar todas las referencias al ID más alto al ID más bajo
-- (Mantener el ID más bajo, eliminar el más alto)

-- Actualizar en T_insignias
UPDATE T_insignias 
SET Estatus = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado')
WHERE Estatus = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado')
AND (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado') != (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');

-- Actualizar en T_insignias_otorgadas
UPDATE T_insignias_otorgadas 
SET Id_Estatus = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado')
WHERE Id_Estatus = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado')
AND (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado') != (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');

-- Paso 3: Eliminar el registro duplicado (el de ID más alto)
-- Usar variable temporal para evitar error de subconsulta
SET @id_eliminar = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado' AND id != (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado'));

DELETE FROM estatus 
WHERE id = @id_eliminar 
AND @id_eliminar IS NOT NULL;

-- Paso 4: Verificar que solo quede un registro
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';

