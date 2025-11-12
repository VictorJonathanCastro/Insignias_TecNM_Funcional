<?php
/**
 * CONFIGURACIÓN SMTP PARA ENVÍO DE CORREOS
 * 
 * IMPORTANTE: Edita estos valores con tus credenciales reales de correo
 * 
 * Para TecNM, normalmente usan:
 * - Office 365 (Outlook)
 * - Gmail
 * - Servidor propio de TecNM
 */

// ============================================
// CONFIGURACIÓN SMTP PRINCIPAL
// ============================================

// Correo desde el cual se enviarán las notificaciones
define('SMTP_FROM_EMAIL', '211230001@smarcos.tecnm.mx');
define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');

// Credenciales SMTP
define('SMTP_USERNAME', '211230001@smarcos.tecnm.mx');
define('SMTP_PASSWORD', 'cas29ye02vi20'); // ⚠️ CAMBIA ESTA CONTRASEÑA

// Servidor SMTP principal (prueba primero este)
define('SMTP_HOST', 'smtp.office365.com'); // Para Office 365
// define('SMTP_HOST', 'smtp.gmail.com'); // Para Gmail
// define('SMTP_HOST', 'mail.tecnm.mx'); // Para servidor TecNM

// Puerto SMTP
define('SMTP_PORT', 587); // Para STARTTLS
// define('SMTP_PORT', 465); // Para SSL

// Tipo de encriptación
define('SMTP_ENCRYPTION', 'tls'); // 'tls' o 'ssl'

// ============================================
// SERVIDORES SMTP ALTERNATIVOS (si el principal falla)
// ============================================
define('SMTP_SERVERS_ALTERNATIVOS', [
    'smtp.office365.com' => ['port' => 587, 'encryption' => 'tls'],
    'smtp-mail.outlook.com' => ['port' => 587, 'encryption' => 'tls'],
    'smtp.gmail.com' => ['port' => 587, 'encryption' => 'tls'],
    'mail.tecnm.mx' => ['port' => 587, 'encryption' => 'tls'],
    'smtp.tecnm.mx' => ['port' => 587, 'encryption' => 'tls'],
    'smtp.smarcos.tecnm.mx' => ['port' => 587, 'encryption' => 'tls'],
]);

// ============================================
// CONFIGURACIÓN ADICIONAL
// ============================================

// Timeout para conexión SMTP (segundos)
define('SMTP_TIMEOUT', 30);

// Habilitar modo debug (muestra errores detallados)
define('SMTP_DEBUG', false); // Cambia a true para ver errores detallados

// Verificar certificados SSL (cambiar a true en producción)
define('SMTP_VERIFY_SSL', false);

