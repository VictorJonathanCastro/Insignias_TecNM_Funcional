-- ========================================
-- BACKUP COMPLETO DEL SISTEMA DE INSIGNIAS TECNM FUNCIONAL
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
-- Tabla: destinatario (Destinatarios) - ESTRUCTURA REAL
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
    FOREIGN KEY (Id_Destinatario) REFERENCES destinatario(id),
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
    d.Nombre AS Nombre_Destinatario,
    d.Apellido_Paterno AS Apellido_Paterno_Destinatario,
    d.Apellido_Materno AS Apellido_Materno_Destinatario,
    d.Correo AS Correo_Destinatario,
    d.Rol AS Rol_Destinatario,
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
JOIN cat_insignias ci ON tin.id = ci.id
JOIN destinatario d ON tio.Id_Destinatario = d.id
JOIN it_centros itc ON ti.Propone_Insignia = itc.id
JOIN estatus e ON ti.Estatus = e.id
JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id;

-- ===============================
-- DATOS ESPECIFICOS DEL SISTEMA ACTUAL
-- ===============================

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
(2, 'Responsabilidad Social', 'RS', '2024-10-01'),
(3, 'Excelencia Academica', 'EA', '2024-10-01'),
(4, 'Innovacion Tecnologica', 'IT', '2024-10-01'),
(5, 'Cultura y Deporte', 'CD', '2024-10-01');

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
(4, '2024-1', '2024-01-01', '2024-06-30', '2024-10-01');

-- Insertar responsables de emisión
INSERT IGNORE INTO responsable_emision (id, Nombre_Completo, Adscripcion, Cargo, Codigo_Identificacion, Correo, Telefono) VALUES
(1, 'Victor Hugo Agaton Catalan', 3, 'Director', 'DIR001', 'victor.agaton@tecnm.mx', '444-123-4567'),
(2, 'Andrea Yadira Zarate Fuentes', 4, 'Secretaria', 'SEC001', 'andrea.zarate@tecnm.mx', '444-234-5678'),
(3, 'Ramon Jimenez Lopez', 2, 'Director General', 'DG001', 'ramon.jimenez@tecnm.mx', '444-345-6789');

-- Insertar destinatarios (DATOS REALES ACTUALES - 11 registros)
INSERT IGNORE INTO destinatario (ID_destinatario, Nombre_Completo, Curp, Matricula, Correo, ITCentro, Fecha_Creacion) VALUES
(1, 'Rigoberto Martinez Villazana', 'MAVR030711HDFSRM01', '211230014', '211230014@smarcos.tecnm.mx', 1, '2025-10-14'),
(2, 'Juan Perez Garcia', NULL, NULL, NULL, 1, '2025-10-14'),
(3, 'Maria Lopez Hernandez', NULL, NULL, NULL, 1, '2025-10-14'),
(4, 'Juan Perez Molina', NULL, NULL, NULL, 1, '2025-10-14'),
(5, 'Alma Yulitzi Benitez Gomez', NULL, NULL, NULL, 1, '2025-10-14'),
(6, 'Tomas Castro Garcia', NULL, NULL, NULL, 1, '2025-10-15'),
(7, 'Juan Carrillo Ortega', NULL, NULL, NULL, 1, '2025-10-18'),
(8, 'Estefania Roman Sanches', 'MARA100723HGRRYRA8', '211230001', '211230001@smarcos.tecnm.mx', 1, '2025-10-19'),
(9, 'Ingrid Roxana Pioquinto Castro', 'RAJZ870614MGRMML00', '211230001', '211230001@smarcos.tecnm.mx', 1, '2025-10-19'),
(10, 'Yeni Castro Sánchez', 'CASY950315MDFRCN01', '211230002', '211230001@smarcos.tecnm.mx', 1, '2025-10-19'),
(11, 'Tomas Perez Urrutia', 'GOUD830514MGRMBC08', '211230012', '211230001@smarcos.tecnm.mx', 1, '2025-10-21');

-- Insertar usuarios del sistema (incluyendo administrador)
INSERT IGNORE INTO Usuario (Id_Usuario, Nombre, Apellido_Paterno, Apellido_Materno, Correo, Contrasena, Rol, Estado, It_Centro_Id) VALUES
(1, 'Administrador', 'del', 'Sistema', 'admin@tecnm.mx', 'admin123', 'Admin', 'Activo', 1),
(2, 'Rigoberto', 'Martinez', 'Villazana', 'rigoberto.martinez@tecnm.mx', 'rigoberto123', 'Estudiante', 'Activo', 1),
(3, 'Juan', 'Perez', 'Garcia', 'juan.perez@tecnm.mx', 'juan123', 'Estudiante', 'Activo', 1),
(4, 'Maria', 'Lopez', 'Hernandez', 'maria.lopez@tecnm.mx', 'maria123', 'Estudiante', 'Activo', 1),
(5, 'Carlos', 'Rodriguez', 'Martinez', 'carlos.rodriguez@tecnm.mx', 'carlos123', 'Estudiante', 'Activo', 1);

-- Insertar firmas digitales (DATOS REALES ACTUALES - 2 registros)
INSERT IGNORE INTO firmas_digitales (id, responsable_id, nombre_responsable, archivo_firma, hash_verificacion, fecha_generacion, activa, fecha_creacion) VALUES
(1, 1, 'Victor Hugo Agaton Catalan', 'firma_1_1761019710.html', 'd63beae608d0527fcaa56a66318b105496b33a2a88dad8814d', '0000-00-00 00:00:00', 1, '2025-10-20 22:08:30'),
(2, 1, 'Andrea Yadira Zarate Fuentes', 'firma_1_1761159864.html', '56aee51cc534f06b6ec929187c50d4f0698b67ffa466720485', '0000-00-00 00:00:00', 1, '2025-10-22 13:04:24');

-- Insertar insignias maestras
INSERT IGNORE INTO T_insignias (id, Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual, Fecha_Creacion_Registro) VALUES
(1, 1, 1, 'Responsabilidad Social', 'Insignia otorgada por demostrar responsabilidad social a traves de la participacion en proyectos comunitarios y actividades de voluntariado que contribuyen al bienestar social.', 'Completar un minimo de 1000 horas de servicio social en proyectos comunitarios reconocidos, demostrar liderazgo en al menos 2 iniciativas sociales, y obtener evaluacion positiva de los beneficiarios.', '2024-10-01', '2024-10-01', 'TecNM-San Marcos', 5, 'responsabilidad_social.png', '2024-10-01'),
(2, 2, 1, 'Liderazgo Estudiantil', 'Insignia otorgada por demostrar liderazgo estudiantil a traves de la organizacion y coordinacion de actividades academicas, culturales o deportivas que beneficien a la comunidad estudiantil.', 'Completar un minimo de 800 horas de liderazgo estudiantil, organizar al menos 3 eventos estudiantiles exitosos, y obtener reconocimiento de la comunidad estudiantil y autoridades academicas.', '2024-10-01', '2024-10-01', 'TecNM-San Marcos', 5, 'liderazgo_estudiantil.png', '2024-10-01'),
(3, 6, 1, 'Movilidad e Intercambio', 'Insignia otorgada por participar exitosamente en programas de movilidad estudiantil nacional o internacional.', 'Completar exitosamente un programa de movilidad estudiantil, mantener promedio academico superior a 8.0, y obtener evaluacion positiva de la institucion receptora.', '2024-10-01', '2024-10-01', 'TecNM-San Marcos', 5, 'movilidad_intercambio.png', '2024-10-01'),
(4, 7, 1, 'Embajador del Deporte', 'Insignia otorgada por representar exitosamente a la institucion en competencias deportivas.', 'Participar en al menos 3 competencias deportivas representando a la institucion, obtener medalla o reconocimiento en al menos una competencia, y mantener promedio academico superior a 7.5.', '2024-10-01', '2024-10-01', 'TecNM-San Marcos', 5, 'embajador_deporte.png', '2024-10-01'),
(5, 8, 1, 'Embajador del Arte', 'Insignia otorgada por representar exitosamente a la institucion en actividades artisticas y culturales.', 'Participar en al menos 3 actividades artisticas representando a la institucion, obtener reconocimiento en al menos una actividad, y mantener promedio academico superior a 7.5.', '2024-10-01', '2024-10-01', 'TecNM-San Marcos', 5, 'embajador_arte.png', '2024-10-01');

-- Insertar insignias otorgadas (DATOS REALES ACTUALES - 13 registros)
INSERT IGNORE INTO T_insignias_otorgadas (id, Id_Insignia, Id_Destinatario, Fecha_Emision, Id_Periodo_Emision, Id_Estatus, Fecha_Creacion_Registro) VALUES
-- Insignias para Rigoberto Martinez Villazana (ID_destinatario = 1)
(1, 1, 1, '2024-10-01', 1, 5, '2024-10-01'),
(2, 2, 1, '2024-10-15', 1, 5, '2024-10-15'),
(3, 3, 1, '2024-11-01', 2, 5, '2024-11-01'),
(4, 4, 1, '2024-11-15', 2, 5, '2024-11-15'),
(5, 5, 1, '2024-12-01', 3, 5, '2024-12-01'),

-- Insignias para otros estudiantes (usando ID_destinatario real)
(6, 1, 2, '2024-10-02', 1, 5, '2024-10-02'),
(7, 2, 3, '2024-10-16', 1, 5, '2024-10-16'),
(8, 3, 4, '2024-11-02', 2, 5, '2024-11-02'),
(9, 4, 5, '2024-11-16', 2, 5, '2024-11-16'),
(10, 5, 2, '2024-12-02', 3, 5, '2024-12-02'),
(11, 1, 8, '2024-10-19', 1, 5, '2024-10-19'),
(12, 2, 9, '2024-10-19', 1, 5, '2024-10-19'),
(13, 3, 10, '2024-10-21', 2, 5, '2024-10-21');

-- ===============================
-- Índices para optimización
-- ===============================
CREATE INDEX idx_t_insignias_tipo ON T_insignias(Tipo_Insignia);
CREATE INDEX idx_t_insignias_centro ON T_insignias(Propone_Insignia);
CREATE INDEX idx_t_insignias_estatus ON T_insignias(Estatus);
CREATE INDEX idx_t_insignias_otorgadas_insignia ON T_insignias_otorgadas(Id_Insignia);
CREATE INDEX idx_t_insignias_otorgadas_destinatario ON T_insignias_otorgadas(Id_Destinatario);
CREATE INDEX idx_t_insignias_otorgadas_periodo ON T_insignias_otorgadas(Id_Periodo_Emision);
CREATE INDEX idx_t_insignias_otorgadas_fecha ON T_insignias_otorgadas(Fecha_Emision);
CREATE INDEX idx_destinatario_centro ON destinatario(ITCentro);
CREATE INDEX idx_destinatario_nombre ON destinatario(Nombre_Completo);
CREATE INDEX idx_destinatario_curp ON destinatario(Curp);
CREATE INDEX idx_destinatario_matricula ON destinatario(Matricula);
CREATE INDEX idx_firmas_responsable ON firmas_digitales(responsable_id);
CREATE INDEX idx_firmas_hash ON firmas_digitales(hash_verificacion);
CREATE INDEX idx_usuario_correo ON Usuario(Correo);
CREATE INDEX idx_usuario_rol ON Usuario(Rol);

