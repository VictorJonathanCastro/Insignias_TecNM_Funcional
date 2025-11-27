-- ========================================
-- CREAR TABLA PARA HISTORIAL DE CARGAS MASIVAS
-- ========================================
-- Esta tabla registra todas las cargas masivas realizadas
-- ========================================

USE insignia;

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
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para búsquedas rápidas
CREATE INDEX idx_fecha_carga ON historial_cargas_masivas(fecha_carga);
CREATE INDEX idx_usuario_id ON historial_cargas_masivas(usuario_id);
CREATE INDEX idx_tipo_carga ON historial_cargas_masivas(tipo_carga);
CREATE INDEX idx_estado ON historial_cargas_masivas(estado);

-- ========================================
-- FIN DEL SCRIPT
-- ========================================

