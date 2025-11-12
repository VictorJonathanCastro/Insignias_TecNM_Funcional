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
define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');

// Credenciales SMTP del SERVIDOR/SISTEMA
// ⚠️ Solo se usan si mail() nativo falla
// ⚠️ Cuando te den el correo sistema.insignias@smarcos.tecnm.mx, actualiza la contraseña aquí
define('SMTP_USERNAME', 'sistema.insignias@smarcos.tecnm.mx'); // ✅ Correo del sistema
define('SMTP_PASSWORD', 'CONTRASEÑA_QUE_TE_DEN_PARA_ESTE_CORREO'); // ⚠️ Actualiza esto cuando te den el correo

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

