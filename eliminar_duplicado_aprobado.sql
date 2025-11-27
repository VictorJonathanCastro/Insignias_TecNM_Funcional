-- ========================================
-- ELIMINAR DUPLICADO "Aprobado" EN estatus
-- ========================================
-- Script directo para eliminar el registro duplicado
-- ========================================

USE insignia;

-- Ver los registros duplicados antes de eliminar
SELECT 'ANTES - Registros con Aprobado:' as estado;
SELECT id, Nombre_Estatus, Acron_Estatus FROM estatus WHERE Nombre_Estatus = 'Aprobado' ORDER BY id;

-- Identificar IDs
SET @id_original = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');
SET @id_duplicado = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado' AND id != @id_original);

-- Mostrar qué IDs se van a usar
SELECT 
    @id_original as id_original_a_mantener,
    @id_duplicado as id_duplicado_a_eliminar;

-- Actualizar referencias en T_insignias
UPDATE T_insignias 
SET Estatus = @id_original 
WHERE Estatus = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Actualizar referencias en T_insignias_otorgadas  
UPDATE T_insignias_otorgadas 
SET Id_Estatus = @id_original 
WHERE Id_Estatus = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Eliminar el duplicado
DELETE FROM estatus 
WHERE id = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Verificar resultado
SELECT 'DESPUÉS - Registros con Aprobado:' as estado;
SELECT id, Nombre_Estatus, Acron_Estatus FROM estatus WHERE Nombre_Estatus = 'Aprobado';

-- Mostrar todos los estatus
SELECT 'Todos los estatus:' as estado;
SELECT * FROM estatus ORDER BY id;

