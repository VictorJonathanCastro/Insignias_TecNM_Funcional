<?php
/**
 * Script de diagnóstico para el servidor remoto
 * Este archivo ayuda a identificar problemas de configuración
 */

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Servidor - Insignias TecNM</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1e3c72; border-bottom: 3px solid #1e3c72; padding-bottom: 10px; }
        h2 { color: #2c5aa0; margin-top: 30px; }
        .check { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; color: #0c5460; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Servidor - Insignias TecNM</h1>
        
        <h2>1. Información del Sistema</h2>
        <div class="info check">
            <strong>SO:</strong> <?php echo PHP_OS; ?><br>
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
            <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?><br>
            <strong>Directorio actual:</strong> <?php echo __DIR__; ?><br>
            <strong>Usuario del proceso:</strong> <?php echo get_current_user(); ?><br>
        </div>

        <h2>2. Verificación de Archivos</h2>
        <?php
        $archivos_requeridos = [
            'conexion.php' => 'Archivo de conexión a base de datos',
            'verificar_sesion.php' => 'Archivo de verificación de sesión',
            'modulo_de_administracion.php' => 'Módulo de administración',
            'login.php' => 'Página de login',
        ];
        
        foreach ($archivos_requeridos as $archivo => $descripcion) {
            $existe = file_exists($archivo);
            $clase = $existe ? 'success' : 'error';
            $icono = $existe ? '✅' : '❌';
            echo "<div class='$clase check'>";
            echo "$icono <strong>$archivo</strong> - $descripcion: ";
            if ($existe) {
                echo "Existe (" . filesize($archivo) . " bytes)";
                echo " - Permisos: " . substr(sprintf('%o', fileperms($archivo)), -4);
            } else {
                echo "NO ENCONTRADO";
            }
            echo "</div>";
        }
        ?>

        <h2>3. Verificación de Entorno</h2>
        <?php
        $es_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        $es_ubuntu = (file_exists('/etc/apache2/') || file_exists('/var/www/html/'));
        
        echo "<div class='info check'>";
        echo "<strong>Entorno detectado:</strong> ";
        if ($es_windows) {
            echo "XAMPP (Windows)";
        } elseif ($es_ubuntu) {
            echo "Ubuntu/Linux";
        } else {
            echo "Desconocido";
        }
        echo "</div>";
        
        echo "<div class='info check'>";
        echo "<strong>Rutas del sistema:</strong><br>";
        echo "/etc/apache2/ existe: " . ($es_ubuntu ? '✅ Sí' : '❌ No') . "<br>";
        echo "/var/www/html/ existe: " . (file_exists('/var/www/html/') ? '✅ Sí' : '❌ No') . "<br>";
        echo "</div>";
        ?>

        <h2>4. Verificación de Conexión a Base de Datos</h2>
        <?php
        if (file_exists('conexion.php')) {
            try {
                include('conexion.php');
                
                if (isset($conexion) && is_object($conexion)) {
                    if ($conexion->connect_errno) {
                        echo "<div class='error check'>";
                        echo "❌ <strong>Error de conexión:</strong> " . $conexion->connect_error . " (Código: " . $conexion->connect_errno . ")";
                        echo "</div>";
                    } else {
                        echo "<div class='success check'>";
                        echo "✅ <strong>Conexión exitosa</strong><br>";
                        echo "Servidor MySQL: " . $conexion->server_info . "<br>";
                        echo "Charset: " . $conexion->character_set_name() . "<br>";
                        echo "Base de datos: " . $conexion->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
                        echo "</div>";
                        
                        // Verificar tablas importantes
                        $tablas_importantes = ['Usuario', 'insignias', 'cat_insignias', 'tipo_insignia'];
                        echo "<div class='info check'>";
                        echo "<strong>Tablas en la base de datos:</strong><br>";
                        $result = $conexion->query("SHOW TABLES");
                        $tablas_existentes = [];
                        while ($row = $result->fetch_row()) {
                            $tablas_existentes[] = $row[0];
                        }
                        foreach ($tablas_importantes as $tabla) {
                            $existe = in_array($tabla, $tablas_existentes);
                            $icono = $existe ? '✅' : '❌';
                            echo "$icono $tabla<br>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<div class='error check'>";
                    echo "❌ <strong>Error:</strong> La variable \$conexion no está definida o no es un objeto";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error check'>";
                echo "❌ <strong>Excepción:</strong> " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        } else {
            echo "<div class='error check'>";
            echo "❌ <strong>Error:</strong> El archivo conexion.php no existe. Necesitas crearlo con las credenciales correctas del servidor.";
            echo "</div>";
        }
        ?>

        <h2>5. Verificación de Sesión</h2>
        <?php
        session_start();
        echo "<div class='info check'>";
        echo "<strong>Estado de sesión:</strong><br>";
        echo "ID de sesión: " . session_id() . "<br>";
        echo "Usuario ID: " . ($_SESSION['usuario_id'] ?? 'No definido') . "<br>";
        echo "Rol: " . ($_SESSION['rol'] ?? 'No definido') . "<br>";
        echo "Nombre: " . ($_SESSION['nombre'] ?? 'No definido') . "<br>";
        echo "</div>";
        ?>

        <h2>6. Verificación de Funciones</h2>
        <?php
        $funciones_requeridas = [
            'verificarRoles',
            'obtenerUsuarioActual',
            'esSuperUsuario',
            'puedeCrearAdministradores',
        ];
        
        if (file_exists('verificar_sesion.php')) {
            include('verificar_sesion.php');
        }
        
        foreach ($funciones_requeridas as $funcion) {
            $existe = function_exists($funcion);
            $clase = $existe ? 'success' : 'error';
            $icono = $existe ? '✅' : '❌';
            echo "<div class='$clase check'>";
            echo "$icono <strong>$funcion()</strong>: " . ($existe ? 'Disponible' : 'NO DISPONIBLE');
            echo "</div>";
        }
        ?>

        <h2>7. Permisos de Archivos</h2>
        <div class="info check">
            <pre><?php
            $archivos = ['conexion.php', 'verificar_sesion.php', 'modulo_de_administracion.php'];
            foreach ($archivos as $archivo) {
                if (file_exists($archivo)) {
                    $perms = fileperms($archivo);
                    $owner = fileowner($archivo);
                    $group = filegroup($archivo);
                    echo "$archivo:\n";
                    echo "  Permisos: " . substr(sprintf('%o', $perms), -4) . "\n";
                    echo "  Propietario: " . (function_exists('posix_getpwuid') ? posix_getpwuid($owner)['name'] : $owner) . "\n";
                    echo "  Grupo: " . (function_exists('posix_getgrgid') ? posix_getgrgid($group)['name'] : $group) . "\n";
                    echo "  Escribible: " . (is_writable($archivo) ? 'Sí' : 'No') . "\n";
                    echo "\n";
                }
            }
            ?></pre>
        </div>

        <h2>8. Logs de Errores</h2>
        <div class="info check">
            <strong>Ruta de error_log:</strong> <?php echo ini_get('error_log'); ?><br>
            <strong>display_errors:</strong> <?php echo ini_get('display_errors') ? 'Activado' : 'Desactivado'; ?><br>
            <strong>log_errors:</strong> <?php echo ini_get('log_errors') ? 'Activado' : 'Desactivado'; ?><br>
        </div>

        <h2>9. Recomendaciones</h2>
        <div class="warning check">
            <?php
            $problemas = [];
            
            if (!file_exists('conexion.php')) {
                $problemas[] = "❌ Crear archivo conexion.php con las credenciales correctas del servidor";
            }
            
            if (file_exists('conexion.php')) {
                include('conexion.php');
                if (isset($conexion) && $conexion->connect_errno) {
                    $problemas[] = "❌ Verificar credenciales de base de datos en conexion.php";
                    $problemas[] = "❌ Verificar que MySQL esté corriendo: sudo systemctl status mysql";
                    $problemas[] = "❌ Verificar que el usuario MySQL tenga permisos: mysql -u root -p -e 'SHOW GRANTS FOR insignia_user@localhost;'";
                }
            }
            
            if (empty($problemas)) {
                echo "✅ <strong>Todo parece estar bien configurado</strong>";
            } else {
                echo "<strong>Problemas detectados:</strong><br>";
                foreach ($problemas as $problema) {
                    echo "$problema<br>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>

