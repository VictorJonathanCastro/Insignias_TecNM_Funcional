# 🔐 Solución: Credenciales SMTP Incorrectas

## Problema Detectado

Los logs muestran que **TODOS los servidores SMTP fallan con "Could not authenticate"**.

Esto significa que:
- ❌ La contraseña `Sistema-Insignias2025` es incorrecta
- ❌ O el correo `sistema.insignias@smarcos.tecnm.mx` no existe
- ❌ O Office 365 requiere contraseña de aplicación (si tiene 2FA)

## Soluciones

### Opción 1: Verificar/Corregir Credenciales (RECOMENDADO)

1. **Verificar que el correo existe:**
   - Confirma que `sistema.insignias@smarcos.tecnm.mx` existe
   - Si no existe, usa otro correo institucional de TecNM

2. **Verificar la contraseña:**
   - La contraseña debe ser la **contraseña REAL** del correo
   - Si el correo tiene 2FA, necesitas una **contraseña de aplicación**

3. **Actualizar config_smtp.php en el servidor:**

```bash
cd /var/www/html
sudo nano config_smtp.php
```

Cambia estas líneas:
```php
define('SMTP_USERNAME', 'TU_CORREO_REAL@smarcos.tecnm.mx');
define('SMTP_PASSWORD', 'TU_CONTRASEÑA_REAL'); // ⚠️ La contraseña correcta
```

### Opción 2: Usar Contraseña de Aplicación (Si tiene 2FA)

Si el correo tiene autenticación de dos factores (2FA):

1. **Generar contraseña de aplicación en Office 365:**
   - Ve a: https://account.microsoft.com/security/app-passwords
   - Genera una contraseña de aplicación
   - Usa esa contraseña en lugar de la contraseña normal

2. **Actualizar config_smtp.php:**
```php
define('SMTP_PASSWORD', 'LA_CONTRASEÑA_DE_APLICACIÓN_GENERADA');
```

### Opción 3: Usar Otro Correo Institucional

Si `sistema.insignias@smarcos.tecnm.mx` no funciona, usa otro correo:

```php
define('SMTP_USERNAME', '211230001@smarcos.tecnm.mx'); // Tu correo
define('SMTP_PASSWORD', 'TU_CONTRASEÑA_REAL'); // Tu contraseña real
```

## Verificar Después de Cambiar

1. **Actualizar en el servidor:**
```bash
cd /var/www/html
sudo git pull origin main
# O editar directamente config_smtp.php
```

2. **Probar de nuevo:**
```
http://158.23.160.163/probar_correo_directo.php
```

3. **Ver logs:**
```bash
sudo tail -n 50 /var/log/apache2/error.log | grep -i "phpmailer\|smtp"
```

Si ahora ves `✅ Correo PHPMailer enviado exitosamente`, ¡funciona en tiempo real!

## Estado Actual

- ❌ **NO funciona en tiempo real** (PHPMailer falla por credenciales incorrectas)
- ⚠️ **Usa mail() nativo** (puede tener retrasos de minutos u horas)
- ✅ **Solución:** Corregir credenciales SMTP en config_smtp.php

