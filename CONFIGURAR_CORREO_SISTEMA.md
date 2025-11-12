# 📧 CONFIGURAR CORREO DEL SISTEMA

## ✅ Cuando tengas el correo: sistema.insignias@smarcos.tecnm.mx

### Paso 1: Editar config_smtp.php

```bash
cd /var/www/html
nano config_smtp.php
```

### Paso 2: Actualizar estas líneas:

```php
// Correo del SISTEMA
define('SMTP_FROM_EMAIL', 'sistema.insignias@smarcos.tecnm.mx');
define('SMTP_FROM_NAME', 'Sistema Insignias TecNM');

// Credenciales SMTP del SISTEMA
define('SMTP_USERNAME', 'sistema.insignias@smarcos.tecnm.mx');
define('SMTP_PASSWORD', 'CONTRASEÑA_QUE_TE_DEN'); // ⚠️ La contraseña que te den para este correo

// Servidor SMTP (probablemente Office 365 si TecNM usa Outlook)
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Paso 3: Probar el correo

Abre en el navegador:
```
http://158.23.160.163/probar_correo.php?correo=TU_CORREO@smarcos.tecnm.mx
```

### Paso 4: Verificar que funciona

Si ves "✅ Función completa funcionó", ¡está listo!

## 🔍 Verificar logs

```bash
tail -n 50 /var/log/apache2/error.log | grep -i "correo\|smtp"
```

Si ves `✅ Correo enviado exitosamente`, ¡funciona al 100%!

## 📝 Nota Importante

- El correo `sistema.insignias@smarcos.tecnm.mx` es del SISTEMA
- Los estudiantes NO necesitan credenciales
- Solo necesitas el correo del estudiante para enviarle la notificación

