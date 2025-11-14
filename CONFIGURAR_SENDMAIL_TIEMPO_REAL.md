# ⚡ Configurar Sendmail para Correo en Tiempo Real

## 🔍 Problema

`mail()` nativo funciona pero puede tener retrasos de 1-5 minutos porque sendmail procesa correos en cola.

## ✅ Solución

Configurar sendmail para enviar inmediatamente usando relay SMTP directo.

---

## 📋 Opción 1: Script Automático (RECOMENDADO)

### En el servidor (PuTTY), ejecuta:

```bash
cd /var/www/html

# Descargar el script
sudo wget https://raw.githubusercontent.com/VictorJonathanCastro/Insignias_TecNM_Funcional/main/configurar_sendmail_tiempo_real.sh

# O si no tienes wget, crea el archivo manualmente
sudo nano configurar_sendmail_tiempo_real.sh
# (Pega el contenido del script)

# Dar permisos de ejecución
sudo chmod +x configurar_sendmail_tiempo_real.sh

# Ejecutar
sudo bash configurar_sendmail_tiempo_real.sh
```

---

## 📋 Opción 2: Configuración Manual

### Paso 1: Instalar sendmail (si no está instalado)

```bash
sudo apt-get update
sudo apt-get install -y sendmail sendmail-bin
```

### Paso 2: Configurar relay SMTP

```bash
# Editar configuración
sudo nano /etc/mail/sendmail.mc

# Agregar estas líneas al final (antes de MAILER_DEFINITIONS):
define(`SMART_HOST', `smtp.tecnm.mx')dnl
define(`RELAY_MAILER', `esmtp')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
FEATURE(`access_db')dnl
FEATURE(`relay_based_on_MX')dnl
```

### Paso 3: Recompilar y reiniciar

```bash
cd /etc/mail
sudo make
sudo systemctl restart sendmail
```

### Paso 4: Verificar

```bash
# Ver estado
sudo systemctl status sendmail

# Probar envío
echo "test" | mail -s "Prueba" root
```

---

## 📋 Opción 3: Configurar Relay SMTP con Autenticación

Si el servidor SMTP de TecNM requiere autenticación:

### Paso 1: Crear archivo de autenticación

```bash
sudo nano /etc/mail/authinfo
```

Agregar:
```
AuthInfo:smtp.tecnm.mx "U:sistema.insignias@smarcos.tecnm.mx" "P:Sistema-Insignias2025" "M:PLAIN"
```

### Paso 2: Generar base de datos de autenticación

```bash
cd /etc/mail
sudo makemap hash authinfo < authinfo
sudo chmod 600 authinfo authinfo.db
```

### Paso 3: Configurar sendmail.mc

```bash
sudo nano /etc/mail/sendmail.mc
```

Agregar:
```
define(`SMART_HOST', `smtp.tecnm.mx')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
define(`confAUTH_MECHANISMS', `EXTERNAL GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl
FEATURE(`authinfo', `hash -o /etc/mail/authinfo.db')dnl
```

### Paso 4: Recompilar y reiniciar

```bash
cd /etc/mail
sudo make
sudo systemctl restart sendmail
```

---

## ✅ Verificar que Funcionó

1. **Probar el correo:**
   ```
   http://158.23.160.163/probar_correo_tiempo_real.php
   ```

2. **Debería mostrar:**
   - ✅ "⚡ CORREO ENVIADO EN TIEMPO REAL" para mail() nativo
   - ✅ "Sistema funcionando al 100% en tiempo real"

3. **Verificar logs:**
   ```bash
   tail -f /var/log/mail.log
   ```

---

## 🔧 Si Aún Hay Problemas

### Verificar configuración de sendmail:

```bash
# Ver configuración actual
sendmail -d0.1 -bv root

# Ver cola de correo
mailq

# Procesar cola manualmente
sendmail -q
```

### Verificar conectividad SMTP:

```bash
# Probar conexión al servidor SMTP
telnet smtp.tecnm.mx 587

# O con openssl
openssl s_client -connect smtp.tecnm.mx:587 -starttls smtp
```

---

## 📝 Nota Importante

- Si el servidor SMTP de TecNM no permite relay sin autenticación, usa la Opción 3
- Si prefieres usar Office 365, cambia `smtp.tecnm.mx` por `smtp-mail.outlook.com` en la configuración
- Después de configurar, el correo debería llegar en menos de 1 minuto

