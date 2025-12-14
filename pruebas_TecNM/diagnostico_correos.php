<?php
/**
 * Diagnóstico de problemas de correo
 */

require_once 'funciones_correo_mejoradas.php';

echo "<h2>🔍 Diagnóstico de Problemas de Correo</h2>";

// Ejecutar diagnóstico
probarConfiguracionCorreo();

echo "<h3>💡 Soluciones Recomendadas:</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>1. 📧 Configurar SMTP en php.ini:</h4>";
echo "<p>Edita el archivo <code>C:\\xampp\\php\\php.ini</code> y agrega:</p>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 4px;'>";
echo "[mail function]\n";
echo "SMTP = smtp.gmail.com\n";
echo "smtp_port = 587\n";
echo "sendmail_from = tu-correo@gmail.com\n";
echo "auth_username = tu-correo@gmail.com\n";
echo "auth_password = tu-contraseña-app";
echo "</pre>";

echo "<h4>2. 🔧 Instalar PHPMailer:</h4>";
echo "<p>Ejecuta en la terminal:</p>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 4px;'>";
echo "composer require phpmailer/phpmailer";
echo "</pre>";

echo "<h4>3. 🌐 Usar Servicio Externo:</h4>";
echo "<p>Considera usar servicios como:</p>";
echo "<ul>";
echo "<li>SendGrid</li>";
echo "<li>Mailgun</li>";
echo "<li>Amazon SES</li>";
echo "</ul>";

echo "<h4>4. 🧪 Probar con Correo Real:</h4>";
echo "<p>Para probar el envío real, usa un correo válido:</p>";
echo "<form method='POST' style='margin-top: 20px;'>";
echo "<input type='email' name='correo_prueba' placeholder='tu-correo@gmail.com' required style='padding: 8px; margin-right: 10px;'>";
echo "<button type='submit' name='enviar_prueba' style='padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px;'>Enviar Prueba</button>";
echo "</form>";
echo "</div>";

// Procesar envío de prueba
if (isset($_POST['enviar_prueba']) && !empty($_POST['correo_prueba'])) {
    $correo_prueba = $_POST['correo_prueba'];
    
    if (validarCorreo($correo_prueba)) {
        echo "<h3>📤 Enviando correo de prueba...</h3>";
        
        $datos_prueba = [
            'estudiante' => 'Estudiante de Prueba',
            'matricula' => '2024TEST',
            'curp' => 'TEST800101HDFRGN01',
            'nombre_insignia' => 'Insignia de Prueba',
            'categoria' => 'Formación Integral',
            'codigo_insignia' => 'TECNM-TEST-2024-001',
            'periodo' => '2024-1',
            'fecha_otorgamiento' => date('Y-m-d'),
            'responsable' => 'Sistema de Prueba',
            'descripcion' => 'Esta es una insignia de prueba para verificar el funcionamiento del sistema de correos.',
            'url_verificacion' => 'http://localhost/Insignias_TecNM_Funcional/verificar_insignia.php?clave=TECNM-TEST-2024-001'
        ];
        
        $enviado = enviarNotificacionInsigniaMejorada($correo_prueba, $datos_prueba);
        
        if ($enviado) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h4>✅ ¡Correo enviado exitosamente!</h4>";
            echo "<p>Revisa tu bandeja de entrada (y carpeta de spam) en: <strong>" . htmlspecialchars($correo_prueba) . "</strong></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h4>❌ Error al enviar correo</h4>";
            echo "<p>El correo no se pudo enviar. Revisa la configuración SMTP o considera usar PHPMailer.</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4>❌ Correo inválido</h4>";
        echo "<p>Por favor, ingresa un correo electrónico válido.</p>";
        echo "</div>";
    }
}

echo "<h3>📋 Información Adicional:</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<p><strong>Nota:</strong> En entornos de desarrollo local (XAMPP), el envío de correos puede no funcionar por defecto.</p>";
echo "<p><strong>Recomendación:</strong> Para producción, usa un servicio de correo profesional como SendGrid o Mailgun.</p>";
echo "</div>";
?>
