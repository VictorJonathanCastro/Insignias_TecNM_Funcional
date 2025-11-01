<?php
// ========================================
// GENERADOR DE DATOS DE PRUEBA MASIVOS
// Sistema de Insignias TecNM
// ========================================

require_once 'conexion.php';

class GeneradorDatosPrueba {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Generar datos de prueba para todas las tablas
     */
    public function generarTodosLosDatos($cantidad = 100) {
        echo "🚀 Generando datos de prueba masivos...\n";
        echo "=====================================\n\n";
        
        $this->generarCentrosIT($cantidad);
        $this->generarDestinatarios($cantidad * 10); // 10 destinatarios por centro
        $this->generarTiposInsignia();
        $this->generarCategoriasInsignia();
        $this->generarPeriodosEmision();
        $this->generarInsignias($cantidad);
        $this->generarInsigniasOtorgadas($cantidad * 5); // 5 insignias por destinatario
        
        echo "\n✅ ¡Generación de datos completada!\n";
        echo "📊 Total de registros generados: " . ($cantidad * 16) . "\n";
    }
    
    /**
     * Generar centros IT de prueba
     */
    private function generarCentrosIT($cantidad) {
        echo "🏫 Generando $cantidad centros IT...\n";
        
        $estados = ['Guanajuato', 'Querétaro', 'Michoacán', 'Estado de México', 'Jalisco', 'Nuevo León', 'Puebla', 'Veracruz'];
        $tipos = ['Federal', 'Descentralizado', 'Estatal'];
        
        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = "Instituto Tecnológico de Ciudad " . $i;
            $acron = "ITC" . str_pad($i, 3, '0', STR_PAD_LEFT);
            $estado = $estados[array_rand($estados)];
            $clave = str_pad($i, 2, '0', STR_PAD_LEFT) . "DIT" . str_pad($i, 4, '0', STR_PAD_LEFT) . chr(65 + ($i % 26));
            $tipo = $tipos[array_rand($tipos)];
            
            $sql = "INSERT INTO it_centros (Nombre_itc, Acron, Estado, Clave_ct, Tipo_itc) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssss", $nombre, $acron, $estado, $clave, $tipo);
            $stmt->execute();
        }
        
        echo "✅ $cantidad centros IT generados\n";
    }
    
    /**
     * Generar destinatarios de prueba
     */
    private function generarDestinatarios($cantidad) {
        echo "👥 Generando $cantidad destinatarios...\n";
        
        $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Laura', 'José', 'Sofía'];
        $apellidos = ['García', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Cruz', 'Flores', 'Herrera'];
        $generos = ['Masculino', 'Femenino'];
        $roles = ['Estudiante', 'Docente', 'Personal Administrativo'];
        
        // Obtener centros disponibles
        $centros = $this->obtenerCentros();
        
        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido_paterno = $apellidos[array_rand($apellidos)];
            $apellido_materno = $apellidos[array_rand($apellidos)];
            $nombre_completo = "$nombre $apellido_paterno $apellido_materno";
            $genero = $generos[array_rand($generos)];
            $rol = $roles[array_rand($roles)];
            $matricula = str_pad($i, 7, '0', STR_PAD_LEFT);
            $correo = strtolower($nombre . '.' . $apellido_paterno . $i . '@tecnm.mx');
            $telefono = '555' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            $curp = $this->generarCURP($nombre, $apellido_paterno, $apellido_materno, $genero);
            $centro_id = $centros[array_rand($centros)];
            
            $sql = "INSERT INTO destinatario (Id_Centro, Nombre_Completo, Nombre, Apellido_Paterno, Apellido_Materno, Genero, Curp, Matricula, Correo, Telefono, Rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("issssssssss", $centro_id, $nombre_completo, $nombre, $apellido_paterno, $apellido_materno, $genero, $curp, $matricula, $correo, $telefono, $rol);
            $stmt->execute();
        }
        
        echo "✅ $cantidad destinatarios generados\n";
    }
    
    /**
     * Generar tipos de insignia
     */
    private function generarTiposInsignia() {
        echo "🏆 Generando tipos de insignia...\n";
        
        $tipos = [
            ['Responsabilidad Social', 'RS'],
            ['Liderazgo Estudiantil', 'LE'],
            ['Innovación Tecnológica', 'IN'],
            ['Emprendimiento', 'EM'],
            ['Sustentabilidad', 'SU'],
            ['Excelencia Académica', 'EA'],
            ['Participación Cultural', 'PC'],
            ['Deporte y Recreación', 'DR'],
            ['Servicio Comunitario', 'SC'],
            ['Investigación Científica', 'IC']
        ];
        
        foreach ($tipos as $tipo) {
            $sql = "INSERT IGNORE INTO tipo_insignia (Nombre_Insignia, Acron_Insignia) VALUES (?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ss", $tipo[0], $tipo[1]);
            $stmt->execute();
        }
        
        echo "✅ Tipos de insignia generados\n";
    }
    
    /**
     * Generar categorías de insignia
     */
    private function generarCategoriasInsignia() {
        echo "📂 Generando categorías de insignia...\n";
        
        $categorias = [
            ['Científica', 'CIEN'],
            ['Cultural', 'CULT'],
            ['Deportiva', 'DEPO'],
            ['Social', 'SOCI'],
            ['Académica', 'ACAD'],
            ['Tecnológica', 'TECH'],
            ['Ambiental', 'AMBI'],
            ['Empresarial', 'EMPR']
        ];
        
        foreach ($categorias as $categoria) {
            $sql = "INSERT IGNORE INTO cat_insignias (Nombre_cat, Acron_cat) VALUES (?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ss", $categoria[0], $categoria[1]);
            $stmt->execute();
        }
        
        echo "✅ Categorías de insignia generadas\n";
    }
    
    /**
     * Generar periodos de emisión
     */
    private function generarPeriodosEmision() {
        echo "📅 Generando periodos de emisión...\n";
        
        $años = range(2020, 2024);
        
        foreach ($años as $año) {
            // Enero-Junio
            $sql = "INSERT IGNORE INTO periodo_emision (Nombre_Periodo, Fecha_Inicio, Fecha_Fin) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $periodo1 = "Enero-Junio $año";
            $inicio1 = "$año-01-01";
            $fin1 = "$año-06-30";
            $stmt->bind_param("sss", $periodo1, $inicio1, $fin1);
            $stmt->execute();
            
            // Julio-Diciembre
            $periodo2 = "Julio-Diciembre $año";
            $inicio2 = "$año-07-01";
            $fin2 = "$año-12-31";
            $stmt->bind_param("sss", $periodo2, $inicio2, $fin2);
            $stmt->execute();
        }
        
        echo "✅ Periodos de emisión generados\n";
    }
    
    /**
     * Generar insignias maestras
     */
    private function generarInsignias($cantidad) {
        echo "🎖️ Generando $cantidad insignias maestras...\n";
        
        $programas = ['Ingeniería en Sistemas', 'Ingeniería Industrial', 'Ingeniería Mecánica', 'Ingeniería Eléctrica', 'Licenciatura en Administración'];
        $centros = $this->obtenerCentros();
        $tipos = $this->obtenerTiposInsignia();
        $estatus = [1, 5]; // Activo, Aprobado
        
        for ($i = 1; $i <= $cantidad; $i++) {
            $tipo_id = $tipos[array_rand($tipos)];
            $centro_id = $centros[array_rand($centros)];
            $programa = $programas[array_rand($programas)];
            $descripcion = "Insignia otorgada por demostrar excelencia en " . strtolower($programa) . " a través de proyectos innovadores y participación destacada en actividades académicas.";
            $criterio = "Completar un mínimo de 1000 horas de actividades relacionadas, demostrar liderazgo en al menos 2 proyectos, y obtener evaluación positiva de profesores y compañeros.";
            $fecha_creacion = date('Y-m-d', strtotime('-' . rand(1, 365) . ' days'));
            $fecha_autorizacion = date('Y-m-d', strtotime($fecha_creacion . ' +' . rand(1, 30) . ' days'));
            $generador = 'TecNM-Sistema';
            $estatus_id = $estatus[array_rand($estatus)];
            $archivo = "insignia_" . $i . ".jpg";
            
            $sql = "INSERT INTO T_insignias (Tipo_Insignia, Propone_Insignia, Programa, Descripcion, Criterio, Fecha_Creacion, Fecha_Autorizacion, Nombre_gen_ins, Estatus, Archivo_Visual) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iissssssis", $tipo_id, $centro_id, $programa, $descripcion, $criterio, $fecha_creacion, $fecha_autorizacion, $generador, $estatus_id, $archivo);
            $stmt->execute();
        }
        
        echo "✅ $cantidad insignias maestras generadas\n";
    }
    
    /**
     * Generar insignias otorgadas
     */
    private function generarInsigniasOtorgadas($cantidad) {
        echo "🏅 Generando $cantidad insignias otorgadas...\n";
        
        $insignias = $this->obtenerInsignias();
        $destinatarios = $this->obtenerDestinatarios();
        $periodos = $this->obtenerPeriodos();
        $estatus = [1, 5]; // Activo, Aprobado
        
        for ($i = 1; $i <= $cantidad; $i++) {
            $insignia_id = $insignias[array_rand($insignias)];
            $destinatario_id = $destinatarios[array_rand($destinatarios)];
            $periodo_id = $periodos[array_rand($periodos)];
            $estatus_id = $estatus[array_rand($estatus)];
            $fecha_emision = date('Y-m-d', strtotime('-' . rand(1, 180) . ' days'));
            
            $sql = "INSERT INTO T_insignias_otorgadas (Id_Insignia, Id_Destinatario, Fecha_Emision, Id_Periodo_Emision, Id_Estatus) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iisii", $insignia_id, $destinatario_id, $fecha_emision, $periodo_id, $estatus_id);
            $stmt->execute();
        }
        
        echo "✅ $cantidad insignias otorgadas generadas\n";
    }
    
    /**
     * Generar CURP de prueba
     */
    private function generarCURP($nombre, $apellido_paterno, $apellido_materno, $genero) {
        $vocales = ['A', 'E', 'I', 'O', 'U'];
        $consonantes = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z'];
        
        $curp = substr($apellido_paterno, 0, 2);
        $curp .= substr($apellido_materno, 0, 1);
        $curp .= substr($nombre, 0, 1);
        $curp .= date('y');
        $curp .= str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $curp .= str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $curp .= ($genero === 'Masculino') ? 'H' : 'M';
        $curp .= $consonantes[array_rand($consonantes)];
        $curp .= $vocales[array_rand($vocales)];
        $curp .= $consonantes[array_rand($consonantes)];
        $curp .= $consonantes[array_rand($consonantes)];
        $curp .= str_pad(rand(0, 9), 1, '0', STR_PAD_LEFT);
        
        return $curp;
    }
    
    /**
     * Obtener IDs de centros
     */
    private function obtenerCentros() {
        $result = $this->conexion->query("SELECT id FROM it_centros");
        $centros = [];
        while ($row = $result->fetch_assoc()) {
            $centros[] = $row['id'];
        }
        return $centros;
    }
    
    /**
     * Obtener IDs de tipos de insignia
     */
    private function obtenerTiposInsignia() {
        $result = $this->conexion->query("SELECT id FROM tipo_insignia");
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row['id'];
        }
        return $tipos;
    }
    
    /**
     * Obtener IDs de destinatarios
     */
    private function obtenerDestinatarios() {
        $result = $this->conexion->query("SELECT id FROM destinatario");
        $destinatarios = [];
        while ($row = $result->fetch_assoc()) {
            $destinatarios[] = $row['id'];
        }
        return $destinatarios;
    }
    
    /**
     * Obtener IDs de insignias
     */
    private function obtenerInsignias() {
        $result = $this->conexion->query("SELECT id FROM T_insignias");
        $insignias = [];
        while ($row = $result->fetch_assoc()) {
            $insignias[] = $row['id'];
        }
        return $insignias;
    }
    
    /**
     * Obtener IDs de periodos
     */
    private function obtenerPeriodos() {
        $result = $this->conexion->query("SELECT id FROM periodo_emision");
        $periodos = [];
        while ($row = $result->fetch_assoc()) {
            $periodos[] = $row['id'];
        }
        return $periodos;
    }
    
    /**
     * Limpiar datos de prueba
     */
    public function limpiarDatosPrueba() {
        echo "🧹 Limpiando datos de prueba...\n";
        
        $tablas = [
            'T_insignias_otorgadas',
            'T_insignias',
            'destinatario',
            'responsable_emision',
            'it_centros',
            'periodo_emision',
            'cat_insignias',
            'tipo_insignia',
            'estatus'
        ];
        
        foreach ($tablas as $tabla) {
            $this->conexion->query("DELETE FROM $tabla WHERE id > 5"); // Mantener datos iniciales
            echo "✅ Tabla $tabla limpiada\n";
        }
        
        echo "✅ Datos de prueba eliminados\n";
    }
}

// Ejecutar si se llama directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $generador = new GeneradorDatosPrueba($conexion);
    
    if (isset($argv[1]) && $argv[1] === 'limpiar') {
        $generador->limpiarDatosPrueba();
    } else {
        $cantidad = isset($argv[1]) ? (int)$argv[1] : 50;
        $generador->generarTodosLosDatos($cantidad);
    }
}
?>
