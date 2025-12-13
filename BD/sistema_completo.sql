-- ========================================
-- SISTEMA COMPLETO DE INSIGNIAS TECNM - SCRIPT SQL CONSOLIDADO
-- SECCIÓN 1: CREACIÓN DE BASE DE DATOS Y ESTRUCTURA PRINCIPAL
-- ========================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS insignia;
USE insignia;

-- ===============================
-- Tabla: tipo_insignia (Tipos de Insignia)
-- ===============================
CREATE TABLE IF NOT EXISTS tipo_insignia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Insignia VARCHAR(255) NOT NULL,
    Acron_Insignia VARCHAR(50),
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE)
);

-- ===============================
-- Tabla: it_centros (Centros IT)
-- ===============================
CREATE TABLE IF NOT EXISTS it_centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_itc VARCHAR(255) NOT NULL,
    Acron VARCHAR(50),
    Estado VARCHAR(100),
    Clave_ct VARCHAR(50),
    Tipo_itc VARCHAR(100),
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE)
);

-- ===============================
-- Tabla: cat_insignias (Categorías de Insignias)
-- ===============================
CREATE TABLE IF NOT EXISTS cat_insignias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_cat VARCHAR(255) NOT NULL,
    Acron_cat VARCHAR(50),
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE)
);

-- ===============================
-- Tabla: estatus (Estados)
-- ===============================
CREATE TABLE IF NOT EXISTS estatus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Estatus VARCHAR(100) NOT NULL,
    Acron_Estatus VARCHAR(50),
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE)
);

-- ===============================
-- Tabla: periodo_emision (Periodos de Emisión)
-- ===============================
CREATE TABLE IF NOT EXISTS periodo_emision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Periodo VARCHAR(100) NOT NULL,
    Fecha_Inicio DATE,
    Fecha_Fin DATE,
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE)
);

-- ===============================
-- Tabla: destinatario (Destinatarios)
-- ===============================
CREATE TABLE IF NOT EXISTS destinatario (
    ID_destinatario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Completo VARCHAR(255) NOT NULL,
    Curp VARCHAR(20),
    Matricula VARCHAR(100),
    Correo VARCHAR(255),
    ITCentro INT NOT NULL,
    Fecha_Creacion DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (ITCentro) REFERENCES it_centros(id)
);

-- ===============================
-- Tabla: responsable_emision (Responsables de Emisión)
-- ===============================
CREATE TABLE IF NOT EXISTS responsable_emision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_Completo VARCHAR(255) NOT NULL,
    Adscripcion INT NOT NULL,
    Cargo VARCHAR(100),
    Codigo_Identificacion VARCHAR(100),
    Correo VARCHAR(255),
    Telefono VARCHAR(20),
    Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Adscripcion) REFERENCES it_centros(id)
);

-- ===============================
-- Tabla: T_insignias (Insignias Maestras)
-- ===============================
CREATE TABLE IF NOT EXISTS T_insignias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Tipo_Insignia INT NOT NULL,
    Propone_Insignia INT NOT NULL,
    Programa VARCHAR(255),
    Descripcion TEXT NOT NULL,
    Criterio TEXT NOT NULL,
    Fecha_Creacion DATE,
    Fecha_Autorizacion DATE,
    Nombre_gen_ins VARCHAR(255),
    Estatus INT NOT NULL,
    Archivo_Visual VARCHAR(255),
    Fecha_Creacion_Registro DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (Tipo_Insignia) REFERENCES tipo_insignia(id),
    FOREIGN KEY (Propone_Insignia) REFERENCES it_centros(id),
    FOREIGN KEY (Estatus) REFERENCES estatus(id)
);

-- ===============================
-- Tabla: T_insignias_otorgadas (Insignias Otorgadas)
-- ===============================
CREATE TABLE IF NOT EXISTS T_insignias_otorgadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Id_Insignia INT NOT NULL,
    Id_Destinatario INT NOT NULL,
    Fecha_Emision DATE,
    Id_Periodo_Emision INT NOT NULL,
    Id_Estatus INT NOT NULL,
    Fecha_Creacion_Registro DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (Id_Insignia) REFERENCES T_insignias(id),
    FOREIGN KEY (Id_Destinatario) REFERENCES destinatario(ID_destinatario),
    FOREIGN KEY (Id_Periodo_Emision) REFERENCES periodo_emision(id),
    FOREIGN KEY (Id_Estatus) REFERENCES estatus(id)
);

-- ===============================
-- Tabla: Usuario (Usuarios del Sistema)
-- ===============================
CREATE TABLE IF NOT EXISTS Usuario (
    Id_Usuario INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Apellido_Paterno VARCHAR(100) NOT NULL,
    Apellido_Materno VARCHAR(100),
    Correo VARCHAR(255) NOT NULL UNIQUE,
    Contrasena VARCHAR(255) NOT NULL,
    Rol ENUM('Admin', 'SuperUsuario', 'Estudiante') NOT NULL DEFAULT 'Estudiante',
    Estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    It_Centro_Id INT,
    Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Fecha_Ultimo_Acceso TIMESTAMP NULL,
    FOREIGN KEY (It_Centro_Id) REFERENCES it_centros(id)
);

-- ===============================
-- Tabla: firmas_digitales (Firmas Digitales)
-- ===============================
CREATE TABLE IF NOT EXISTS firmas_digitales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsable_id INT NOT NULL,
    nombre_responsable VARCHAR(255) NOT NULL,
    archivo_firma VARCHAR(255) NOT NULL,
    hash_verificacion VARCHAR(255) NOT NULL,
    fecha_generacion DATETIME NOT NULL,
    activa TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_responsable (responsable_id),
    INDEX idx_hash (hash_verificacion),
    INDEX idx_activa (activa)
);

-- ========================================
-- SECCIÓN 2: TABLAS ADICIONALES
-- ========================================

-- ===============================
-- Tabla: historial_cargas_masivas
-- ===============================
CREATE TABLE IF NOT EXISTS historial_cargas_masivas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_carga VARCHAR(100) NOT NULL COMMENT 'centros_it, destinatarios, insignias_otorgadas, etc.',
    usuario_id INT NOT NULL,
    usuario_nombre VARCHAR(255),
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_registros INT DEFAULT 0,
    registros_exitosos INT DEFAULT 0,
    registros_actualizados INT DEFAULT 0,
    registros_con_error INT DEFAULT 0,
    tamanio_archivo BIGINT COMMENT 'Tamaño del archivo en bytes',
    ruta_archivo VARCHAR(500) COMMENT 'Ruta donde se guardó el archivo (opcional)',
    observaciones TEXT,
    estado ENUM('completado', 'con_errores', 'fallido') DEFAULT 'completado',
    FOREIGN KEY (usuario_id) REFERENCES Usuario(Id_Usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para historial_cargas_masivas
CREATE INDEX idx_fecha_carga ON historial_cargas_masivas(fecha_carga);
CREATE INDEX idx_usuario_id ON historial_cargas_masivas(usuario_id);
CREATE INDEX idx_tipo_carga ON historial_cargas_masivas(tipo_carga);
CREATE INDEX idx_estado ON historial_cargas_masivas(estado);

-- ===============================
-- Tabla: insigniasotorgadas (Tabla alternativa)
-- ===============================
CREATE TABLE IF NOT EXISTS insigniasotorgadas (
    ID_otorgada INT AUTO_INCREMENT PRIMARY KEY,
    Codigo_Insignia VARCHAR(255) NOT NULL UNIQUE,
    Destinatario INT NOT NULL,
    Periodo_Emision INT,
    Responsable_Emision INT,
    Estatus INT,
    Fecha_Emision DATE,
    Fecha_Vencimiento DATE,
    Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (Codigo_Insignia),
    INDEX idx_destinatario (Destinatario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SECCIÓN 3: MODIFICACIONES A TABLAS EXISTENTES
-- ========================================

-- ===============================
-- Agregar campos de correo electrónico a it_centros
-- ===============================
ALTER TABLE it_centros 
ADD COLUMN IF NOT EXISTS CE_dir VARCHAR(255) NULL COMMENT 'Correo electrónico de la dirección del plantel',
ADD COLUMN IF NOT EXISTS CE_svin VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección de vinculación del plantel',
ADD COLUMN IF NOT EXISTS CE_saca VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección académica del plantel',
ADD COLUMN IF NOT EXISTS CE_sadm VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección administrativa del plantel',
ADD COLUMN IF NOT EXISTS CE_dvin VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de vinculación del plantel',
ADD COLUMN IF NOT EXISTS CE_dcyd VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de comunicación y difusión del plantel';

-- ===============================
-- Agregar relación entre categorías y subcategorías (Cat_ins en tipo_insignia)
-- ===============================
ALTER TABLE tipo_insignia 
ADD COLUMN IF NOT EXISTS Cat_ins INT NULL;

-- Agregar foreign key si no existe
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = 'insignia' 
                  AND TABLE_NAME = 'tipo_insignia' 
                  AND CONSTRAINT_NAME = 'fk_tipo_insignia_cat_ins');
                  
SET @sql = IF(@fk_exists = 0, 
    'ALTER TABLE tipo_insignia ADD CONSTRAINT fk_tipo_insignia_cat_ins FOREIGN KEY (Cat_ins) REFERENCES cat_insignias(id)',
    'SELECT "Foreign key ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- SECCIÓN 4: VISTAS
-- ========================================

-- ===============================
-- Vista: T_metadatos_completos (Metadatos Completos)
-- ===============================
CREATE OR REPLACE VIEW T_metadatos_completos AS
SELECT 
    tio.id,
    CONCAT(ti.id, ' # ', pe.Nombre_Periodo) AS Codigo_Identificacion,
    CONCAT(ti.id, ' # ', tin.Nombre_Insignia) AS Nombre_Insignia_TecNM,
    CONCAT(ti.id, ' # ', ci.Nombre_cat) AS Categoria_Insignia_TecNM,
    CONCAT(ti.id, ' # ', d.Nombre_Completo) AS Destinatario,
    d.Nombre_Completo AS Nombre_Destinatario,
    NULL AS Apellido_Paterno_Destinatario,
    NULL AS Apellido_Materno_Destinatario,
    d.Correo AS Correo_Destinatario,
    NULL AS Rol_Destinatario,
    CONCAT(ti.id, ' # ', itc.Nombre_itc) AS Institucion_IT_TecNM,
    itc.Nombre_itc AS Nombre_IT_TecNM,
    itc.Acron AS Acron_IT_TecNM,
    ti.Programa AS Programa_Academico,
    ti.Descripcion AS Descripcion_Insignia,
    ti.Criterio AS Criterios_Emision,
    CONCAT(ti.id, ' # ', ti.Archivo_Visual) AS Url_Imagen,
    ti.Fecha_Creacion AS Fecha_Creacion_Insignia,
    ti.Fecha_Autorizacion AS Fecha_Autorizacion_Insignia,
    ti.Nombre_gen_ins AS Nombre_Generador_Insignia,
    CONCAT(ti.id, ' # ', e.Nombre_Estatus) AS Estatus_Insignia,
    CONCAT(ti.id, ' # ', pe.Nombre_Periodo) AS Periodo_Emision,
    tio.Fecha_Emision AS Fecha_Emision_Insignia,
    tio.Fecha_Creacion_Registro
FROM T_insignias_otorgadas tio
JOIN T_insignias ti ON tio.Id_Insignia = ti.id
JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
LEFT JOIN cat_insignias ci ON tin.Cat_ins = ci.id
JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
JOIN it_centros itc ON ti.Propone_Insignia = itc.id
JOIN estatus e ON ti.Estatus = e.id
JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id;

-- ========================================
-- SECCIÓN 5: DATOS INICIALES
-- ========================================

-- Insertar tipos de insignias
INSERT IGNORE INTO tipo_insignia (id, Nombre_Insignia, Acron_Insignia, Fecha_Creacion) VALUES
(1, 'Responsabilidad Social', 'RS', '2024-10-01'),
(2, 'Liderazgo Estudiantil', 'LE', '2024-10-01'),
(3, 'Innovacion', 'IN', '2024-10-01'),
(4, 'Emprendimiento', 'EM', '2024-10-01'),
(5, 'Sustentabilidad', 'SU', '2024-10-01'),
(6, 'Movilidad e Intercambio', 'MI', '2024-10-01'),
(7, 'Embajador del Deporte', 'ED', '2024-10-01'),
(8, 'Embajador del Arte', 'EA', '2024-10-01'),
(9, 'Formacion y Actualizacion', 'FA', '2024-10-01'),
(10, 'Talento Cientifico', 'TC', '2024-10-01');

-- Insertar centros IT
INSERT IGNORE INTO it_centros (id, Nombre_itc, Acron, Estado, Clave_ct, Tipo_itc, Fecha_Creacion) VALUES
(1, 'Instituto Tecnologico de San Marcos', 'ITSM', 'San Luis Potosi', '24DIT0001A', 'Federal', '2024-10-01'),
(2, 'TecNM Central', 'TECNM', 'Ciudad de Mexico', '09DIT0001B', 'Federal', '2024-10-01'),
(3, 'Director de San Marcos', 'DIR-SM', 'San Luis Potosi', '24DIT0002C', 'Federal', '2024-10-01'),
(4, 'Secretaria de Vinculacion y Extension', 'SVE', 'Ciudad de Mexico', '09DIT0002D', 'Federal', '2024-10-01');

-- Insertar categorías de insignias
INSERT IGNORE INTO cat_insignias (id, Nombre_cat, Acron_cat, Fecha_Creacion) VALUES
(1, 'Formacion Integral', 'FI', '2024-10-01'),
(2, 'Docencia', 'DOC', '2024-10-01'),
(3, 'Academia', 'ACA', '2024-10-01');

-- Insertar estatus
INSERT IGNORE INTO estatus (id, Nombre_Estatus, Acron_Estatus, Fecha_Creacion) VALUES
(1, 'Activo', 'ACT', '2024-10-01'),
(2, 'Inactivo', 'INA', '2024-10-01'),
(3, 'Pendiente', 'PEN', '2024-10-01'),
(4, 'Rechazado', 'REC', '2024-10-01'),
(5, 'Aprobado', 'APR', '2024-10-01');

-- Insertar periodos de emisión
INSERT IGNORE INTO periodo_emision (id, Nombre_Periodo, Fecha_Inicio, Fecha_Fin, Fecha_Creacion) VALUES
(1, '2025-1', '2025-01-01', '2025-06-30', '2024-10-01'),
(2, '2025-2', '2025-07-01', '2025-12-31', '2024-10-01'),
(3, '2024-2', '2024-07-01', '2024-12-31', '2024-10-01'),
(4, '2024-1', '2024-01-01', '2024-06-30', '2024-10-01'),
(5, '2026-1', '2026-01-01', '2026-06-30', CURDATE()),
(6, '2026-2', '2026-07-01', '2026-12-31', CURDATE()),
(7, '2027-1', '2027-01-01', '2027-06-30', CURDATE()),
(8, '2027-2', '2027-07-01', '2027-12-31', CURDATE()),
(9, '2028-1', '2028-01-01', '2028-06-30', CURDATE()),
(10, '2028-2', '2028-07-01', '2028-12-31', CURDATE()),
(11, '2029-1', '2029-01-01', '2029-06-30', CURDATE()),
(12, '2029-2', '2029-07-01', '2029-12-31', CURDATE()),
(13, '2030-1', '2030-01-01', '2030-06-30', CURDATE()),
(14, '2030-2', '2030-07-01', '2030-12-31', CURDATE());

-- Insertar responsables de emisión
INSERT IGNORE INTO responsable_emision (id, Nombre_Completo, Adscripcion, Cargo, Codigo_Identificacion, Correo, Telefono) VALUES
(1, 'Victor Hugo Agaton Catalan', 3, 'Director', 'DIR001', 'victor.agaton@tecnm.mx', '444-123-4567'),
(2, 'Andrea Yadira Zarate Fuentes', 4, 'Secretaria', 'SEC001', 'andrea.zarate@tecnm.mx', '444-234-5678'),
(3, 'Ramon Jimenez Lopez', 2, 'Director General', 'DG001', 'ramon.jimenez@tecnm.mx', '444-345-6789');

-- Insertar usuarios del sistema
INSERT IGNORE INTO Usuario (Id_Usuario, Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado, It_Centro_Id) VALUES
(1, 'Administrador', 'del', 'Sistema', 'admin@tecnm.mx', 'admin123', 'Admin', 'Activo', 1),
(2, 'Rigoberto', 'Martinez', 'Villazana', 'rigoberto.martinez@tecnm.mx', 'rigoberto123', 'Estudiante', 'Activo', 1),
(3, 'Juan', 'Perez', 'Garcia', 'juan.perez@tecnm.mx', 'juan123', 'Estudiante', 'Activo', 1),
(4, 'Maria', 'Lopez', 'Hernandez', 'maria.lopez@tecnm.mx', 'maria123', 'Estudiante', 'Activo', 1),
(5, 'Carlos', 'Rodriguez', 'Martinez', 'carlos.rodriguez@tecnm.mx', 'carlos123', 'Estudiante', 'Activo', 1);

-- ========================================
-- SECCIÓN 6: ACTUALIZACIONES Y CORRECCIONES
-- ========================================

-- ===============================
-- Actualizar categorías en el servidor
-- ===============================
-- Eliminar categorías antiguas que ya no se usan (si existen)
DELETE FROM cat_insignias WHERE id IN (4, 5);

-- Actualizar las categorías existentes
UPDATE cat_insignias SET Nombre_cat = 'Formacion Integral', Acron_cat = 'FI' WHERE id = 1;
UPDATE cat_insignias SET Nombre_cat = 'Docencia', Acron_cat = 'DOC' WHERE id = 2;
UPDATE cat_insignias SET Nombre_cat = 'Academia', Acron_cat = 'ACA' WHERE id = 3;

-- Si no existen, insertarlas
INSERT IGNORE INTO cat_insignias (id, Nombre_cat, Acron_cat, Fecha_Creacion) VALUES
(1, 'Formacion Integral', 'FI', '2024-10-01'),
(2, 'Docencia', 'DOC', '2024-10-01'),
(3, 'Academia', 'ACA', '2024-10-01');

-- Asignar tipos de insignia a Formación Integral (id=1)
UPDATE tipo_insignia SET Cat_ins = 1 WHERE id IN (7, 8, 1, 6, 3);
-- 7 = Embajador del Deporte
-- 8 = Embajador del Arte
-- 1 = Responsabilidad Social
-- 6 = Movilidad e Intercambio
-- 3 = Innovacion (Talento Innovador)

-- Asignar tipos de insignia a Docencia (id=2)
UPDATE tipo_insignia SET Cat_ins = 2 WHERE id = 9;
-- 9 = Formacion y Actualizacion

-- Asignar tipos de insignia a Academia (id=3)
UPDATE tipo_insignia SET Cat_ins = 3 WHERE id = 10;
-- 10 = Talento Cientifico

-- ===============================
-- Corregir duplicado "Aprobado" en estatus
-- ===============================
-- Identificar los duplicados
SET @id_mantener = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');
SET @id_eliminar = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado' AND id != @id_mantener);

-- Actualizar referencias en T_insignias
UPDATE T_insignias 
SET Estatus = @id_mantener 
WHERE Estatus = @id_eliminar 
AND @id_eliminar IS NOT NULL 
AND @id_eliminar != @id_mantener;

-- Actualizar referencias en T_insignias_otorgadas
UPDATE T_insignias_otorgadas 
SET Id_Estatus = @id_mantener 
WHERE Id_Estatus = @id_eliminar 
AND @id_eliminar IS NOT NULL 
AND @id_eliminar != @id_mantener;

-- Eliminar el registro duplicado
DELETE FROM estatus 
WHERE id = @id_eliminar 
AND @id_eliminar IS NOT NULL 
AND @id_eliminar != @id_mantener;

-- ===============================
-- Actualizar usuario admin
-- ===============================
-- Opción 1: Si existe admin@tecnm.mx, actualizar su correo
UPDATE Usuario 
SET Correo = 'sistema.insignias@smarcos.tecnm.mx',
    Contrasena = 'Admin292',
    Nombre = 'Victor Jonathan',
    Apellido_Paterno = 'Castro',
    Apellido_Materno = 'Secundino'
WHERE Correo = 'admin@tecnm.mx';

-- Opción 2: Crear nuevo usuario si no existe
INSERT INTO Usuario (
    Nombre, 
    Apellido_Paterno, 
    Apellido_Materno, 
    Correo, 
    Contrasena, 
    Rol, 
    Estado, 
    It_Centro_Id
) 
SELECT 
    'Victor Jonathan',
    'Castro',
    'Secundino',
    'sistema.insignias@smarcos.tecnm.mx',
    'Admin292',
    'Admin',
    'Activo',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM Usuario WHERE Correo = 'sistema.insignias@smarcos.tecnm.mx'
);

-- ========================================
-- SECCIÓN 7: ÍNDICES PARA OPTIMIZACIÓN
-- ========================================

CREATE INDEX IF NOT EXISTS idx_t_insignias_tipo ON T_insignias(Tipo_Insignia);
CREATE INDEX IF NOT EXISTS idx_t_insignias_centro ON T_insignias(Propone_Insignia);
CREATE INDEX IF NOT EXISTS idx_t_insignias_estatus ON T_insignias(Estatus);
CREATE INDEX IF NOT EXISTS idx_t_insignias_otorgadas_insignia ON T_insignias_otorgadas(Id_Insignia);
CREATE INDEX IF NOT EXISTS idx_t_insignias_otorgadas_destinatario ON T_insignias_otorgadas(Id_Destinatario);
CREATE INDEX IF NOT EXISTS idx_t_insignias_otorgadas_periodo ON T_insignias_otorgadas(Id_Periodo_Emision);
CREATE INDEX IF NOT EXISTS idx_t_insignias_otorgadas_fecha ON T_insignias_otorgadas(Fecha_Emision);
CREATE INDEX IF NOT EXISTS idx_destinatario_centro ON destinatario(ITCentro);
CREATE INDEX IF NOT EXISTS idx_destinatario_nombre ON destinatario(Nombre_Completo);
CREATE INDEX IF NOT EXISTS idx_destinatario_curp ON destinatario(Curp);
CREATE INDEX IF NOT EXISTS idx_destinatario_matricula ON destinatario(Matricula);
CREATE INDEX IF NOT EXISTS idx_firmas_responsable ON firmas_digitales(responsable_id);
CREATE INDEX IF NOT EXISTS idx_firmas_hash ON firmas_digitales(hash_verificacion);
CREATE INDEX IF NOT EXISTS idx_usuario_correo ON Usuario(Correo);
CREATE INDEX IF NOT EXISTS idx_usuario_rol ON Usuario(Rol);

-- ========================================
-- SECCIÓN 8: COMANDOS DE VERIFICACIÓN (OPCIONAL)
-- ========================================
-- Descomentar las siguientes líneas para ejecutar verificaciones

/*
-- Verificar estructura de cat_insignias (Categorías)
SHOW COLUMNS FROM cat_insignias;
SELECT * FROM cat_insignias ORDER BY id;

-- Verificar estructura de tipo_insignia (Subcategorías/Insignias)
SHOW COLUMNS FROM tipo_insignia;
SELECT * FROM tipo_insignia ORDER BY id;

-- Ver la relación entre tipo_insignia y cat_insignias
SELECT 
    ti.id as tipo_id,
    ti.Nombre_Insignia as nombre_insignia,
    ti.Cat_ins as categoria_id,
    ci.Nombre_cat as nombre_categoria
FROM tipo_insignia ti
LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
ORDER BY ci.Nombre_cat, ti.Nombre_Insignia;

-- Verificar estructura de insigniasotorgadas
DESCRIBE insigniasotorgadas;
SELECT COUNT(*) as total_registros FROM insigniasotorgadas;

-- Verificar estatus (debe haber solo un "Aprobado")
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';

-- Verificar usuario admin
SELECT 
    Id_Usuario,
    CONCAT(Nombre, ' ', Apellido_Paterno, ' ', Apellido_Materno) AS Nombre_Completo,
    Correo,
    Rol,
    Estado
FROM Usuario 
WHERE Correo = 'sistema.insignias@smarcos.tecnm.mx';

-- Verificar campos de correo en it_centros
SHOW COLUMNS FROM it_centros LIKE 'CE_%';
*/

-- ========================================
-- FIN DEL SCRIPT CONSOLIDADO
-- ========================================

