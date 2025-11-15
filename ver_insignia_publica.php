<?php
require_once 'conexion.php';

// Obtener el código de la insignia desde la URL
$codigo_insignia = $_GET['insignia'] ?? '';

if (empty($codigo_insignia)) {
    die('Código de insignia no válido');
}

// Obtener datos de la insignia
$insignia = null;

try {
    // Verificar qué tabla existe - PRIORIDAD: usar insigniasotorgadas primero (donde se guardan las nuevas insignias)
    $tabla_existe_i = $conexion->query("SHOW TABLES LIKE 'insigniasotorgadas'");
    $usar_tabla_i = ($tabla_existe_i && $tabla_existe_i->num_rows > 0);
    
    $tabla_existe_t = $conexion->query("SHOW TABLES LIKE 'T_insignias_otorgadas'");
    $usar_tabla_t = ($tabla_existe_t && $tabla_existe_t->num_rows > 0);
    
    if ($usar_tabla_i) {
        // Usar insigniasotorgadas (donde se guardan las nuevas insignias)
        $check_destinatario_id = $conexion->query("SHOW COLUMNS FROM destinatario LIKE 'id'");
        $tiene_id_destinatario = ($check_destinatario_id && $check_destinatario_id->num_rows > 0);
        $campo_id_destinatario = $tiene_id_destinatario ? 'id' : 'ID_destinatario';
        
        $sql = "
            SELECT 
                io.ID_otorgada as id,
                io.Codigo_Insignia as codigo,
                io.Fecha_Emision as fecha_emision,
                d.Nombre_Completo as destinatario,
                d.Curp as curp,
                d.Matricula as matricula,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%ART%' THEN 'Embajador del Arte'
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Embajador del Deporte'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' THEN 'Talento Científico'
                    WHEN io.Codigo_Insignia LIKE '%INN%' THEN 'Talento Innovador'
                    WHEN io.Codigo_Insignia LIKE '%SOC%' THEN 'Responsabilidad Social'
                    WHEN io.Codigo_Insignia LIKE '%FOR%' THEN 'Formación y Actualización'
                    WHEN io.Codigo_Insignia LIKE '%MOV%' THEN 'Movilidad e Intercambio'
                    ELSE 'Insignia TecNM'
                END as nombre_insignia,
                CASE 
                    WHEN io.Codigo_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN io.Codigo_Insignia LIKE '%TAL%' OR io.Codigo_Insignia LIKE '%INN%' OR io.Codigo_Insignia LIKE '%FOR%' THEN 'Desarrollo Académico'
                    WHEN io.Codigo_Insignia LIKE '%ART%' OR io.Codigo_Insignia LIKE '%SOC%' OR io.Codigo_Insignia LIKE '%MOV%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria
            FROM insigniasotorgadas io
            LEFT JOIN destinatario d ON io.Destinatario = d." . $campo_id_destinatario . "
            WHERE io.Codigo_Insignia = ?
        ";
    } elseif ($usar_tabla_t) {
        // Usar T_insignias_otorgadas con JOIN a T_insignias
        $sql = "
            SELECT 
                tio.id,
                CONCAT(ti.id, '-', pe.Nombre_Periodo) as codigo,
                tio.Fecha_Emision as fecha_emision,
                d.Nombre_Completo as destinatario,
                d.Curp as curp,
                d.Matricula as matricula,
                COALESCE(tin.Nombre_Insignia, 'Insignia TecNM') as nombre_insignia,
                CASE 
                    WHEN tin.Nombre_Insignia LIKE '%Deporte%' OR tin.Nombre_Insignia LIKE '%EMB%' THEN 'Desarrollo Personal'
                    WHEN tin.Nombre_Insignia LIKE '%Científico%' OR tin.Nombre_Insignia LIKE '%Innovación%' OR tin.Nombre_Insignia LIKE '%Formación%' THEN 'Desarrollo Académico'
                    WHEN tin.Nombre_Insignia LIKE '%Arte%' OR tin.Nombre_Insignia LIKE '%Social%' OR tin.Nombre_Insignia LIKE '%Movilidad%' THEN 'Formación Integral'
                    ELSE 'Formación Integral'
                END as categoria
            FROM T_insignias_otorgadas tio
            LEFT JOIN T_insignias ti ON tio.Id_Insignia = ti.id
            LEFT JOIN tipo_insignia tin ON ti.Tipo_Insignia = tin.id
            LEFT JOIN destinatario d ON tio.Id_Destinatario = d.ID_destinatario
            LEFT JOIN periodo_emision pe ON tio.Id_Periodo_Emision = pe.id
            WHERE CONCAT(ti.id, '-', pe.Nombre_Periodo) = ?
        ";
    } else {
        // Si no existe ninguna tabla, mostrar error
        die('Error: No se encontró ninguna tabla de insignias otorgadas. Verifica que exista T_insignias_otorgadas o insigniasotorgadas en la base de datos.');
    }
    
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $codigo_insignia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $insignia = $result->fetch_assoc();
        }
        $stmt->close();
    }
} catch (Exception $e) {
    die('Error al obtener datos de la insignia: ' . $e->getMessage());
}

if (!$insignia) {
    die('Insignia no encontrada');
}

// Determinar la ruta de la imagen basada en el código de insignia
$imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png'; // Por defecto
if (strpos($insignia['codigo'], 'ART') !== false) {
    $imagen_path = 'imagen/Insignias/EmbajadordelArte.png';
} elseif (strpos($insignia['codigo'], 'EMB') !== false) {
    $imagen_path = 'imagen/Insignias/EmbajadordelDeporte.png';
} elseif (strpos($insignia['codigo'], 'TAL') !== false) {
    $imagen_path = 'imagen/Insignias/TalentoCientifico.png';
} elseif (strpos($insignia['codigo'], 'INN') !== false) {
    $imagen_path = 'imagen/Insignias/TalentoInnovador.png';
} elseif (strpos($insignia['codigo'], 'SOC') !== false) {
    $imagen_path = 'imagen/Insignias/ResponsabilidadSocial.png';
} elseif (strpos($insignia['codigo'], 'FOR') !== false) {
    $imagen_path = 'imagen/Insignias/FormacionyActualizacion.png';
} elseif (strpos($insignia['codigo'], 'MOV') !== false) {
    $imagen_path = 'imagen/Insignias/MovilidadeIntercambio.png';
}

// Obtener IP del servidor
$server_ip = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (empty($server_ip) || $server_ip === '::1') {
    $server_ip = 'localhost';
}
$port = $_SERVER['SERVER_PORT'] ?? '80';
$base_url = "http://" . $server_ip . ($port != '80' ? ':' . $port : '');

// URL de validación
$url_validacion = $base_url . "/Insignias_TecNM_Funcional/verificar_insignia.php?clave=" . urlencode($insignia['codigo']);

// Generar QR usando servicio alternativo (más confiable)
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url_validacion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Validación'); ?></title>
    
    <!-- Meta tags para redes sociales (Open Graph) -->
    <meta property="og:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia TecNM'); ?>">
    <meta property="og:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia['destinatario'] ?? ''); ?>">
    <meta property="og:image" content="<?php echo $base_url . '/' . $imagen_path; ?>">
    <meta property="og:url" content="<?php echo $base_url; ?>/ver_insignia_publica.php?insignia=<?php echo urlencode($insignia['codigo']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TecNM Insignias">
    
    <!-- Meta tags para Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Insignia TecNM - <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia TecNM'); ?>">
    <meta name="twitter:description" content="Insignia otorgada a <?php echo htmlspecialchars($insignia['destinatario'] ?? ''); ?>">
    <meta name="twitter:image" content="<?php echo $base_url . '/' . $imagen_path; ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1b396a 0%, #002855 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #1b396a;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            background: white;
            padding: 40px;
            border-radius: 0 0 15px 15px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .validation-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
        }

        .section-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #1b396a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .insignia-image {
            width: 250px;
            height: 250px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin: 0 auto;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            display: block;
        }

        a:hover .insignia-image {
            transform: scale(1.05);
            cursor: pointer;
        }

        .qr-code {
            text-align: center;
        }

        .qr-code img {
            width: 300px;
            height: 300px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            color: white;
        }

        .btn-success {
            background: #25D366;
        }

        .btn-primary {
            background: #1877F2;
        }

        .btn-info {
            background: #1DA1F2;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .validation-link-section {
            grid-column: 1 / -1;
        }

        .validation-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .actions-section {
            grid-column: 1 / -1;
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .validation-link-section, .actions-section {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Validación de Insignia</h1>
            <p>Sistema de Verificación TecNM</p>
        </div>

        <div class="content">
            <!-- Imagen de la Insignia -->
            <div class="validation-section">
                <div class="section-title">
                    🏆 Imagen de la Insignia
                </div>
                <div style="text-align: center; padding: 20px;">
                    <a href="ver_insignia_completa_publica.php?insignia=<?php echo urlencode($insignia['codigo']); ?>&solo=1" style="text-decoration: none; cursor: pointer;">
                        <div class="insignia-image" style="background-image: url('<?php echo $imagen_path; ?>');" title="Haz clic para ver el certificado completo"></div>
                    </a>
                    <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                        Insignia: <?php echo htmlspecialchars($insignia['nombre_insignia'] ?? 'Insignia de Reconocimiento'); ?><br>
                        <small style="color: #999;">⚠️ Haz clic en la imagen para ver el certificado completo</small>
                    </p>
                </div>
            </div>

            <!-- Código QR de Validación -->
            <div class="validation-section">
                <div class="section-title">
                    📱 Código QR de Validación
                </div>
                <div class="qr-code">
                    <div style="position: relative; display: inline-block;">
                        <img id="qr-img" src="<?php echo $qr_url; ?>" alt="Código QR" style="max-width: 300px; display: block;" onerror="this.style.border='5px solid red'; console.log('Error loading QR from: <?php echo addslashes($qr_url); ?>');">
                        <img src="<?php echo $imagen_path; ?>" alt="Insignia" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60px; height: 60px; background: white; border-radius: 8px; padding: 5px; border: 2px solid #1b396a;">
                    </div>
                    <p style="margin-top: 15px; font-size: 14px; color: #6c757d;">
                        Escanea este código QR para verificar la autenticidad de la insignia
                    </p>
                </div>
            </div>

            <!-- Enlace de Validación -->
            <div class="validation-section validation-link-section">
                <div class="section-title">
                    🔗 Enlace de Validación
                </div>
                <input type="text" value="<?php echo htmlspecialchars($url_validacion); ?>" class="validation-input" readonly id="validationLink">
                <button onclick="copiarEnlace()" class="btn btn-success" style="display: block; width: 100%;">
                    📋 Copiar Enlace
                </button>
                <p style="margin-top: 15px; font-size: 14px; color: #6c757d; text-align: center;">
                    Comparte este enlace para que otros puedan verificar la insignia
                </p>
            </div>

            <!-- Compartir en Redes Sociales -->
            <div class="actions-section" style="background: #f8f9fa; margin-bottom: 20px;">
                <div class="section-title" style="color: #1b396a;">
                    📱 Compartir en Redes Sociales
                </div>
                <div class="action-buttons">
                    <a href="https://wa.me/?text=<?php echo urlencode('🎖️ ¡He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM! 👨‍🎓 ' . htmlspecialchars($insignia['destinatario']) . ' 🏆 Ver mi insignia: ' . $base_url . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo'])); ?>" class="btn btn-success" target="_blank">
                        💬 WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($base_url . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo'])); ?>" class="btn btn-primary" target="_blank">
                        🔵 Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('🎖️ ¡He recibido una insignia de ' . $insignia['nombre_insignia'] . ' del TecNM! 👨‍🎓'); ?>&url=<?php echo urlencode($base_url . '/ver_insignia_publica.php?insignia=' . urlencode($insignia['codigo'])); ?>" class="btn btn-info" target="_blank">
                        🐤 Twitter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copiarEnlace() {
            const linkInput = document.getElementById('validationLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(linkInput.value).then(function() {
                alert('✅ Enlace copiado al portapapeles');
            }, function(err) {
                alert('❌ Error al copiar: ' + err);
            });
        }
    </script>
</body>
</html>