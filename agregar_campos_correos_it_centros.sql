-- ========================================
-- AGREGAR CAMPOS DE CORREO ELECTRÓNICO A LA TABLA it_centros
-- ========================================
-- Este script agrega los campos necesarios para almacenar los correos
-- electrónicos de las diferentes áreas del plantel para la difusión de insignias.
-- 
-- Fecha: 2024
-- Tabla: it_centros
-- ========================================

USE insignia;

-- Agregar campo: Correo electrónico de la Dirección del plantel
ALTER TABLE it_centros 
ADD COLUMN CE_dir VARCHAR(255) NULL COMMENT 'Correo electrónico de la dirección del plantel';

-- Agregar campo: Correo electrónico de la Subdirección de Vinculación
ALTER TABLE it_centros 
ADD COLUMN CE_svin VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección de vinculación del plantel';

-- Agregar campo: Correo electrónico de la Subdirección Académica
ALTER TABLE it_centros 
ADD COLUMN CE_saca VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección académica del plantel';

-- Agregar campo: Correo electrónico de la Subdirección Administrativa
ALTER TABLE it_centros 
ADD COLUMN CE_sadm VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección administrativa del plantel';

-- Agregar campo: Correo electrónico del Departamento de Vinculación
ALTER TABLE it_centros 
ADD COLUMN CE_dvin VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de vinculación del plantel';

-- Agregar campo: Correo electrónico del Departamento de Comunicación y Difusión
ALTER TABLE it_centros 
ADD COLUMN CE_dcyd VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de comunicación y difusión del plantel';

-- ========================================
-- VERIFICACIÓN: Mostrar la estructura actualizada de la tabla
-- ========================================
-- Descomentar la siguiente línea para verificar la estructura:
-- DESCRIBE it_centros;

-- ========================================
-- FIN DEL SCRIPT
-- ========================================

