# 📧 SOLUCIÓN: Correo Sin Credenciales del Estudiante

## ✅ Lo Correcto

**NO necesitas las credenciales del estudiante.** Solo necesitas:
1. **Correo del estudiante** (para enviarle la notificación)
2. **Servidor SMTP funcionando** (para enviar el correo)

## 🎯 OPCIONES (de más fácil a más compleja)

### OPCIÓN 1: Usar mail() nativo (RECOMENDADO - NO requiere credenciales)

El sistema primero intenta `mail()` nativo que NO requiere credenciales SMTP.

**Ventajas:**
- ✅ No requiere credenciales
- ✅ No requiere config_smtp.php
- ✅ Funciona si sendmail está instalado

**Instalar sendmail en el servidor:**
```bash
sudo apt-get update
sudo apt-get install -y sendmail
sudo systemctl restart sendmail
```

**Verificar si funciona:**
```bash
php -r "mail('test@ejemplo.com', 'Prueba', 'Mensaje de prueba');"
```

### OPCIÓN 2: Crear correo del sistema

Si TecNM puede crear un correo del sistema:
- `sistema.insignias@smarcos.tecnm.mx`
- `noreply@smarcos.tecnm.mx`
- `insignias@smarcos.tecnm.mx`

Luego edita `config_smtp.php` con ese correo y su contraseña.

### OPCIÓN 3: Usar servicio SMTP externo (si TecNM lo permite)

Servicios como:
- SendGrid (gratis hasta 100 correos/día)
- Mailgun (gratis hasta 5,000 correos/mes)
- Amazon SES (muy económico)

## 📝 Configuración Actual

El sistema funciona así:

1. **Primero intenta:** `mail()` nativo (NO requiere credenciales)
2. **Si falla:** PHPMailer con SMTP (requiere config_smtp.php)
3. **Si todo falla:** Simulación (guarda en archivo)

## 🔧 Para que funcione al 100%

**Mejor opción:** Instalar sendmail en el servidor
```bash
sudo apt-get install -y sendmail
sudo systemctl restart sendmail
```

Con esto, el correo funcionará sin necesidad de `config_smtp.php` ni credenciales.

