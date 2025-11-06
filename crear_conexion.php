#!/usr/bin/env php
<?php
/**
 * Script para crear conexion.php automáticamente en el servidor
 * Ejecutar con: sudo php crear_conexion.php
 */

echo "=== Creando conexion.php automáticamente ===\n\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('conexion.php.example')) {
    echo "ERROR: No se encuentra conexion.php.example\n";
    echo "Asegúrate de estar en /var/www/html\n";
    exit(1);
}

// Leer el archivo de ejemplo
$contenido = file_get_contents('conexion.php.example');

// Verificar que la contraseña está configurada correctamente para Ubuntu
$patron_password = '/case\s+[\'"]ubuntu[\'"]:.*?\$password\s*=\s*["\']([^"\']*)["\']/s';
if (preg_match($patron_password, $contenido, $matches)) {
    $password_actual = $matches[1];
    echo "Contraseña encontrada en ejemplo: " . ($password_actual ? "SÍ" : "VACÍA") . "\n";
    
    if (empty($password_actual) || $password_actual !== 'InsigniaTecNM2024!') {
        echo "⚠️  ADVERTENCIA: La contraseña en conexion.php.example no es la esperada\n";
        echo "   Actualizando contraseña...\n";
        
        // Reemplazar la contraseña
        $contenido = preg_replace(
            '/(case\s+[\'"]ubuntu[\'"]:.*?\$password\s*=\s*["\'])([^"\']*)(["\'])/s',
            '$1InsigniaTecNM2024!$3',
            $contenido
        );
    }
}

// Copiar el contenido a conexion.php
if (file_put_contents('conexion.php', $contenido)) {
    echo "✓ conexion.php creado exitosamente\n";
} else {
    echo "✗ ERROR: No se pudo crear conexion.php\n";
    echo "   Intenta ejecutar: sudo cp conexion.php.example conexion.php\n";
    exit(1);
}

// Establecer permisos correctos
chmod('conexion.php', 0644);
chown('conexion.php', 'www-data');
chgrp('conexion.php', 'www-data');

echo "✓ Permisos configurados correctamente\n";

// Verificar que el archivo se creó correctamente
if (file_exists('conexion.php')) {
    echo "\n=== Verificación ===\n";
    
    // Verificar que detecta Ubuntu
    require_once 'conexion.php';
    
    if (function_exists('obtenerEntorno')) {
        $entorno = obtenerEntorno();
        echo "Entorno detectado: " . $entorno . "\n";
        
        if ($entorno === 'ubuntu') {
            echo "✓ Entorno Ubuntu detectado correctamente\n";
        } else {
            echo "⚠️  ADVERTENCIA: No se detectó Ubuntu. Detección: " . $entorno . "\n";
        }
    }
    
    // Verificar que la contraseña no está vacía
    if (isset($password) && !empty($password)) {
        echo "✓ Contraseña configurada: " . str_repeat('*', strlen($password)) . "\n";
    } else {
        echo "✗ ERROR: La contraseña está vacía\n";
        exit(1);
    }
    
    // Probar conexión
    echo "\n=== Probando conexión ===\n";
    if (isset($conexion) && !$conexion->connect_errno) {
        echo "✓ Conexión exitosa a MySQL\n";
        echo "  Base de datos: " . $bd . "\n";
        echo "  Usuario: " . $usuario . "\n";
        echo "  Servidor: " . $servidor . "\n";
    } else {
        echo "✗ ERROR: No se pudo conectar a MySQL\n";
        if (isset($conexion)) {
            echo "  Error: " . $conexion->connect_error . "\n";
        }
    }
}

echo "\n=== Proceso completado ===\n";

