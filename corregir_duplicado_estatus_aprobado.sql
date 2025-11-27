-- ========================================
-- CORREGIR DUPLICADO DE "Aprobado" EN LA TABLA estatus
-- ========================================
-- Este script identifica y elimina el registro duplicado de "Aprobado"
-- manteniendo solo el registro con el ID más bajo (el original).
-- 
-- IMPORTANTE: Antes de ejecutar, verifica qué registros están usando
-- el ID que se va a eliminar para actualizarlos.
-- ========================================

USE insignia;

-- ========================================
-- PASO 1: Identificar los duplicados
-- ========================================
-- Ver todos los registros con "Aprobado"
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado' ORDER BY id;

-- Ver cuántos registros hay con "Aprobado"
SELECT COUNT(*) as total_duplicados 
FROM estatus 
WHERE Nombre_Estatus = 'Aprobado';

-- ========================================
-- PASO 2: Verificar qué tablas están usando los IDs de estatus
-- ========================================
-- Verificar uso en T_insignias
SELECT 'T_insignias' as tabla, Estatus as id_estatus, COUNT(*) as total_registros
FROM T_insignias 
WHERE Estatus IN (SELECT id FROM estatus WHERE Nombre_Estatus = 'Aprobado')
GROUP BY Estatus;

-- Verificar uso en T_insignias_otorgadas
SELECT 'T_insignias_otorgadas' as tabla, Id_Estatus as id_estatus, COUNT(*) as total_registros
FROM T_insignias_otorgadas 
WHERE Id_Estatus IN (SELECT id FROM estatus WHERE Nombre_Estatus = 'Aprobado')
GROUP BY Id_Estatus;

-- Verificar uso en otras tablas que puedan tener referencia a estatus
-- (Ajusta según las tablas de tu base de datos)

-- ========================================
-- PASO 3: Identificar el ID que se va a mantener (el más bajo)
-- ========================================
SET @id_mantener = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');
SET @id_eliminar = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');

SELECT 
    @id_mantener as id_a_mantener,
    @id_eliminar as id_a_eliminar;

-- ========================================
-- PASO 4: Actualizar referencias al ID que se va a eliminar
-- ========================================
-- IMPORTANTE: Solo ejecuta estos comandos si @id_eliminar es diferente de NULL
-- y si hay registros que necesiten actualizarse

-- Actualizar en T_insignias
UPDATE T_insignias 
SET Estatus = @id_mantener 
WHERE Estatus = @id_eliminar;

-- Actualizar en T_insignias_otorgadas
UPDATE T_insignias_otorgadas 
SET Id_Estatus = @id_mantener 
WHERE Id_Estatus = @id_eliminar;

-- Si tienes otras tablas con referencia a estatus, actualízalas aquí
-- Ejemplo:
-- UPDATE otra_tabla SET estatus_id = @id_mantener WHERE estatus_id = @id_eliminar;

-- ========================================
-- PASO 5: Eliminar el registro duplicado
-- ========================================
-- Solo elimina si hay más de un registro con "Aprobado"
DELETE FROM estatus 
WHERE id = @id_eliminar 
AND @id_eliminar IS NOT NULL 
AND @id_eliminar != @id_mantener;

-- ========================================
-- PASO 6: Verificación final
-- ========================================
-- Verificar que solo quede un registro "Aprobado"
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';

-- Ver todos los estatus para confirmar
SELECT * FROM estatus ORDER BY id;

-- ========================================
-- FIN DEL SCRIPT
-- ========================================

