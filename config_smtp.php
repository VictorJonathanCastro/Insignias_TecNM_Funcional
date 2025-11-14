<?php
/**
 * CONFIGURACIÓN SMTP PARA ENVÍO DE CORREOS
 * 
 * IMPORTANTE: Estas son las credenciales del SERVIDOR/SISTEMA que ENVÍA los correos
 * NO son las credenciales de los estudiantes (ellos solo reciben las notificaciones)
 * 
 * Para TecNM, normalmente usan:
 * - Office 365 (Outlook)
 * - Gmail
 * - Servidor propio de TecNM
 */

// ============================================
// CONFIGURACIÓN SMTP PRINCIPAL
// ============================================

// ============================================
// ⚠️ IMPORTANTE: LEE ESTO PRIMERO
// ============================================
// Este archivo SOLO se usa si mail() nativo falla
// El sistema primero intenta mail() nativo (NO requiere credenciales)
// Solo si mail() falla, se usa PHPMailer con estas credenciales
//
// OPCIONES:
// 1. Instalar sendmail en el servidor (recomendado) - NO necesita este archivo
// 2. Usar un correo del SISTEMA (no personal) con permisos SMTP
// 3. Dejar este archivo vacío y solo usar mail() nativo
// ============================================

// Correo del SISTEMA desde el cual se enviarán las notificaciones
// ⚠️ IMPORTANTE: Debe ser un correo del SISTEMA, NO personal
// 
// ✅ CORREO DEL SISTEMA: sistema.insignias@smarcos.tecnm.mx
// (Este correo será creado por TecNM)
//
// ❌ NO usar: correos personales de estudiantes o administradores
define('SMTP_FROM_EMAIL', 'sistema.insignias@smarcos.tecnm.mx'); // ✅ Correo del sistema
define('SMTP_FROM_NAME', 'sistema insignias');

// Credenciales SMTP del SERVIDOR/SISTEMA
// ⚠️ Solo se usan si mail() nativo falla
// ⚠️ Cuando te den el correo sistema.insignias@smarcos.tecnm.mx, actualiza la contraseña aquí
define('SMTP_USERNAME', 'sistema.insignias@smarcos.tecnm.mx'); // ✅ Correo del sistema
define('SMTP_PASSWORD', 'Sistema-Insignias2025'); // ✅ Contraseña configurada

// Servidor SMTP principal (prueba primero este)
// PRIORIDAD: Probar primero servidores de TecNM SIN autenticación (más rápido)
define('SMTP_HOST', 'smtp.tecnm.mx'); // Para servidor TecNM (RECOMENDADO - probar primero SIN auth)
// define('SMTP_HOST', 'mail.tecnm.mx'); // Alternativa TecNM
// define('SMTP_HOST', 'smtp.smarcos.tecnm.mx'); // TecNM específico
// define('SMTP_HOST', 'smtp-mail.outlook.com'); // Office 365 (requiere contraseña de aplicación)
// define('SMTP_HOST', 'smtp.office365.com'); // Alternativa Office 365
// define('SMTP_HOST', 'smtp.gmail.com'); // Para Gmail
// define('SMTP_HOST', 'smtp.sendgrid.net'); // SendGrid (si tienes cuenta)
// define('SMTP_HOST', 'smtp.mailgun.org'); // Mailgun (si tienes cuenta)

// Puerto SMTP
define('SMTP_PORT', 587); // Para STARTTLS
// define('SMTP_PORT', 465); // Para SSL

// Tipo de encriptación
define('SMTP_ENCRYPTION', 'tls'); // 'tls' o 'ssl'

// ============================================
// SERVIDORES SMTP ALTERNATIVOS (si el principal falla)
// ============================================
define('SMTP_SERVERS_ALTERNATIVOS', [
    // PRIORIDAD 1: Servidores de TecNM (probablemente más confiables y sin autenticación moderna)
    'smtp.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => false], // Sin auth primero
    'mail.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => false],
    'smtp.smarcos.tecnm.mx' => ['port' => 587, 'encryption' => 'tls', 'auth' => false],
    // PRIORIDAD 2: Office 365 (puede requerir contraseña de aplicación)
    'smtp-mail.outlook.com' => ['port' => 587, 'encryption' => 'tls', 'auth' => true], // Office 365 (más confiable)
    'smtp.office365.com' => ['port' => 587, 'encryption' => 'tls', 'auth' => true], // Office 365 alternativo
    // ÚLTIMO RECURSO
    'smtp.gmail.com' => ['port' => 587, 'encryption' => 'tls', 'auth' => true], // Gmail como último recurso
]);

// ============================================
// SERVICIOS SMTP EXTERNOS (OPCIONAL - más confiables)
// ============================================
// Si TecNM permite usar servicios externos, descomenta y configura:

// SendGrid (gratis hasta 100 correos/día)
// define('SMTP_SERVICIO_EXTERNO', 'sendgrid');
// define('SMTP_HOST', 'smtp.sendgrid.net');
// define('SMTP_USERNAME', 'apikey'); // Siempre 'apikey' para SendGrid
// define('SMTP_PASSWORD', 'TU_API_KEY_DE_SENDGRID'); // Tu API key de SendGrid
// define('SMTP_PORT', 587);

// Mailgun (gratis hasta 5,000 correos/mes)
// define('SMTP_SERVICIO_EXTERNO', 'mailgun');
// define('SMTP_HOST', 'smtp.mailgun.org');
// define('SMTP_USERNAME', 'postmaster@TU_DOMINIO.mailgun.org');
// define('SMTP_PASSWORD', 'TU_PASSWORD_DE_MAILGUN');
// define('SMTP_PORT', 587);

// Amazon SES (muy económico)
// define('SMTP_SERVICIO_EXTERNO', 'ses');
// define('SMTP_HOST', 'email-smtp.REGION.amazonaws.com'); // Ej: email-smtp.us-east-1.amazonaws.com
// define('SMTP_USERNAME', 'TU_ACCESS_KEY');
// define('SMTP_PASSWORD', 'TU_SECRET_KEY');
// define('SMTP_PORT', 587);

// ============================================
// CONFIGURACIÓN ADICIONAL
// ============================================

// Timeout para conexión SMTP (segundos)
define('SMTP_TIMEOUT', 30);

// Habilitar modo debug (muestra errores detallados)
define('SMTP_DEBUG', false); // Deshabilitado - mail() nativo funciona correctamente

// Verificar certificados SSL (cambiar a true en producción)
define('SMTP_VERIFY_SSL', false);

