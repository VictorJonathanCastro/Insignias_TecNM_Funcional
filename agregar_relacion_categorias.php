<?php
/**
 * Script para agregar la relación entre categorías y subcategorías
 * Ejecuta el SQL para agregar la columna Cat_ins y asignar las relaciones
 */

require_once 'conexion.php';
$conexion->select_db("insignia");

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Relación Categorías - Insignias TecNM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1b396a;
            border-bottom: 2px solid #1b396a;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #1b396a;
            color: white;
        }
        .btn {
            background: #1b396a;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Agregar Relación entre Categorías y Subcategorías</h1>
        
        <?php
        // Verificar si ya existe la columna Cat_ins
        $check_cat_ins = $conexion->query("SHOW COLUMNS FROM tipo_insignia LIKE 'Cat_ins'");
        $tiene_cat_ins = ($check_cat_ins && $check_cat_ins->num_rows > 0);
        
        if ($tiene_cat_ins) {
            echo "<div class='info'><strong>✅ La columna Cat_ins ya existe en tipo_insignia</strong></div>";
            
            // Verificar si ya hay relaciones asignadas
            $check_relaciones = $conexion->query("SELECT COUNT(*) as total FROM tipo_insignia WHERE Cat_ins IS NOT NULL");
            $total_relaciones = $check_relaciones->fetch_assoc()['total'];
            
            if ($total_relaciones > 0) {
                echo "<div class='info'><strong>✅ Ya hay $total_relaciones tipos de insignias con categoría asignada</strong></div>";
            } else {
                echo "<div class='error'><strong>⚠️ La columna existe pero no hay relaciones asignadas. Procediendo a asignarlas...</strong></div>";
                $tiene_cat_ins = false; // Forzar asignación
            }
        }
        
        if (!$tiene_cat_ins) {
            echo "<h2>Paso 1: Agregar columna Cat_ins</h2>";
            
            // Agregar columna Cat_ins
            $sql_agregar_columna = "ALTER TABLE tipo_insignia ADD COLUMN Cat_ins INT NULL";
            
            if ($conexion->query($sql_agregar_columna)) {
                echo "<div class='success'><strong>✅ Columna Cat_ins agregada exitosamente</strong></div>";
                
                // Agregar foreign key si es posible
                try {
                    $sql_fk = "ALTER TABLE tipo_insignia ADD FOREIGN KEY (Cat_ins) REFERENCES cat_insignias(id)";
                    $conexion->query($sql_fk);
                    echo "<div class='success'><strong>✅ Foreign key agregada exitosamente</strong></div>";
                } catch (Exception $e) {
                    echo "<div class='info'><strong>ℹ️ Foreign key no se pudo agregar (puede que ya exista): " . htmlspecialchars($e->getMessage()) . "</strong></div>";
                }
            } else {
                echo "<div class='error'><strong>❌ Error al agregar columna: " . htmlspecialchars($conexion->error) . "</strong></div>";
            }
        }
        
        echo "<h2>Paso 2: Asignar relaciones</h2>";
        
        // Asignar cada tipo de insignia a su categoría correspondiente
        $asignaciones = [
            // Formación Integral (id=1)
            ['tipo_id' => 6, 'cat_id' => 1, 'nombre' => 'Movilidad e Intercambio'],
            ['tipo_id' => 8, 'cat_id' => 1, 'nombre' => 'Embajador del Arte'],
            ['tipo_id' => 7, 'cat_id' => 1, 'nombre' => 'Embajador del Deporte'],
            
            // Responsabilidad Social (id=2)
            ['tipo_id' => 1, 'cat_id' => 2, 'nombre' => 'Responsabilidad Social'],
            
            // Excelencia Académica (id=3)
            ['tipo_id' => 9, 'cat_id' => 3, 'nombre' => 'Formacion y Actualizacion'],
            ['tipo_id' => 10, 'cat_id' => 3, 'nombre' => 'Talento Cientifico'],
            
            // Innovación Tecnológica (id=4)
            ['tipo_id' => 3, 'cat_id' => 4, 'nombre' => 'Innovacion'],
            ['tipo_id' => 4, 'cat_id' => 4, 'nombre' => 'Emprendimiento'],
            
            // Cultura y Deporte (id=5)
            ['tipo_id' => 2, 'cat_id' => 5, 'nombre' => 'Liderazgo Estudiantil'],
            ['tipo_id' => 5, 'cat_id' => 5, 'nombre' => 'Sustentabilidad'],
        ];
        
        $exitosos = 0;
        $errores = 0;
        
        foreach ($asignaciones as $asignacion) {
            $sql_update = "UPDATE tipo_insignia SET Cat_ins = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql_update);
            
            if ($stmt) {
                $stmt->bind_param("ii", $asignacion['cat_id'], $asignacion['tipo_id']);
                if ($stmt->execute()) {
                    $exitosos++;
                } else {
                    $errores++;
                    echo "<div class='error'><strong>❌ Error al asignar {$asignacion['nombre']}: " . htmlspecialchars($stmt->error) . "</strong></div>";
                }
                $stmt->close();
            } else {
                $errores++;
                echo "<div class='error'><strong>❌ Error al preparar consulta para {$asignacion['nombre']}: " . htmlspecialchars($conexion->error) . "</strong></div>";
            }
        }
        
        if ($exitosos > 0) {
            echo "<div class='success'><strong>✅ $exitosos relaciones asignadas exitosamente</strong></div>";
        }
        
        if ($errores > 0) {
            echo "<div class='error'><strong>❌ $errores errores al asignar relaciones</strong></div>";
        }
        
        echo "<h2>Paso 3: Verificar resultados</h2>";
        
        // Mostrar todas las relaciones
        $sql_verificar = "SELECT 
                            ti.id,
                            ti.Nombre_Insignia,
                            ti.Cat_ins,
                            ci.Nombre_cat as Categoria
                         FROM tipo_insignia ti
                         LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id
                         ORDER BY ci.Nombre_cat, ti.Nombre_Insignia";
        
        $result = $conexion->query($sql_verificar);
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre Insignia</th><th>ID Categoría</th><th>Categoría</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $categoria = $row['Categoria'] ?? '<span style="color: red;">SIN CATEGORÍA</span>';
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Nombre_Insignia']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Cat_ins'] ?? 'NULL') . "</td>";
                echo "<td>" . $categoria . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Verificar si quedan sin categoría
        $check_sin_categoria = $conexion->query("SELECT COUNT(*) as total FROM tipo_insignia WHERE Cat_ins IS NULL");
        $sin_categoria = $check_sin_categoria->fetch_assoc()['total'];
        
        if ($sin_categoria > 0) {
            echo "<div class='error'><strong>⚠️ Aún hay $sin_categoria tipos de insignias sin categoría asignada</strong></div>";
        } else {
            echo "<div class='success'><strong>✅ Todas las insignias tienen categoría asignada</strong></div>";
        }
        ?>
        
        <div style="margin-top: 30px;">
            <a href="metadatos_formulario.php" class="btn">← Volver al Formulario de Metadatos</a>
            <a href="modulo_de_administracion.php" class="btn">← Volver al Módulo</a>
        </div>
    </div>
</body>
</html>

