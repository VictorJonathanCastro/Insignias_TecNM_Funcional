# 🔧 Solucionar Problemas de Sendmail

## Problema Detectado

Sendmail está instalado y corriendo, pero **NO puede enviar correos** porque:
- ❌ No puede resolver el servidor `smtp.tecnm.mx` (Host unknown)
- ⚠️ El archivo de unidad cambió y necesita recarga

## Solución 1: Recargar systemd (Primero)

```bash
# Recargar las unidades de systemd
sudo systemctl daemon-reload

# Reiniciar sendmail
sudo systemctl restart sendmail

# Verificar que esté corriendo
sudo systemctl status sendmail
```

## Solución 2: Configurar Sendmail para usar un servidor SMTP externo

Si `smtp.tecnm.mx` no se puede resolver, necesitas configurar sendmail para usar un servidor SMTP que funcione:

### Opción A: Configurar Smart Host en Sendmail

```bash
# Editar configuración de sendmail
sudo nano /etc/mail/sendmail.mc

# Buscar la línea que dice:
# define(`SMART_HOST', `smtp.ufl.edu')dnl

# Descomentar y cambiar por:
define(`SMART_HOST', `smtp.office365.com')dnl

# O usar Gmail:
# define(`SMART_HOST', `smtp.gmail.com')dnl

# Regenerar configuración
sudo m4 /etc/mail/sendmail.mc > /etc/mail/sendmail.cf

# Reiniciar sendmail
sudo systemctl restart sendmail
```

### Opción B: Usar PHPMailer directamente (RECOMENDADO)

Ya que sendmail no puede resolver el servidor SMTP, es mejor usar PHPMailer directamente con las credenciales en `config_smtp.php`.

El sistema ya está configurado para hacer esto automáticamente:
1. Intenta PHPMailer primero (si `config_smtp.php` existe)
2. Si falla, intenta mail() nativo (sendmail)
3. Si falla, usa simulación

## Solución 3: Verificar DNS

```bash
# Verificar si el servidor puede resolver smtp.tecnm.mx
nslookup smtp.tecnm.mx

# O con dig
dig smtp.tecnm.mx

# Si no se resuelve, el problema es de DNS del servidor
```

## Solución 4: Configurar Sendmail para usar Office 365 o Gmail

Si TecNM usa Office 365, configura sendmail para usarlo:

```bash
# Editar /etc/mail/sendmail.mc
sudo nano /etc/mail/sendmail.mc

# Agregar estas líneas:
define(`SMART_HOST', `smtp.office365.com')dnl
define(`RELAY_MAILER', `esmtp')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
define(`confAUTH_MECHANISMS', `EXTERNAL GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl

# Regenerar configuración
sudo m4 /etc/mail/sendmail.mc > /etc/mail/sendmail.cf

# Reiniciar
sudo systemctl restart sendmail
```

## ⚠️ RECOMENDACIÓN: Usar PHPMailer directamente

Dado que sendmail tiene problemas de DNS, la mejor solución es:

1. **Asegurar que `config_smtp.php` tenga credenciales válidas**
2. **El sistema automáticamente usará PHPMailer** (que funciona mejor con servidores SMTP externos)

### Verificar que PHPMailer funciona:

```bash
# En el navegador, abre:
http://158.23.160.163/Insignias_TecNM_Funcional/probar_correo.php?correo=TU_CORREO@ejemplo.com
```

## Verificar después de los cambios

```bash
# Ver logs de sendmail
sudo tail -f /var/log/mail.log

# Probar envío
echo "Test" | mail -s "Prueba" tu_correo@ejemplo.com

# Ver si se envió
sudo tail -n 20 /var/log/mail.log
```

