# ✅ Verificar Archivos en el Servidor

Ejecuta estos comandos en PuTTY para verificar que todos los archivos estén presentes:

## 1. Verificar archivos principales

```bash
# Verificar que existe probar_correo.php
ls -la probar_correo.php

# Verificar que existe config_smtp.php
ls -la config_smtp.php

# Verificar que existe funciones_correo_real.php
ls -la funciones_correo_real.php

# Verificar que existe registrar_reconocimiento.php
ls -la registrar_reconocimiento.php
```

## 2. Verificar contenido de config_smtp.php

```bash
# Ver las primeras líneas para verificar que tiene Office 365
head -50 config_smtp.php | grep -i "smtp-mail.outlook"

# O ver toda la configuración SMTP
grep -A 5 "SMTP_HOST" config_smtp.php
```

## 3. Verificar permisos

```bash
# Ver permisos de los archivos
ls -la *.php | head -20

# Si los permisos están mal, corregirlos:
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

## 4. Verificar que el archivo probar_correo.php tiene el contenido correcto

```bash
# Ver las primeras líneas del archivo
head -20 probar_correo.php

# Verificar que incluye funciones_correo_real.php
grep "funciones_correo_real" probar_correo.php
```

## 5. Si probar_correo.php NO existe

Si el comando `ls -la probar_correo.php` dice "No such file or directory", entonces:

```bash
# Ver qué archivos PHP hay
ls -la *.php | wc -l

# Ver si hay algún archivo de prueba de correo
ls -la *correo*.php

# Verificar el estado de Git
git status

# Ver archivos no rastreados
git status --short
```

## 6. Si el archivo existe pero no se accede por web

Verificar configuración de Apache:

```bash
# Ver configuración de Apache
sudo apache2ctl -S

# Ver logs de Apache
sudo tail -n 50 /var/log/apache2/error.log

# Reiniciar Apache si es necesario
sudo systemctl restart apache2
```

