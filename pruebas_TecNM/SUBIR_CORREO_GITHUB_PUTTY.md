# 📤 Subir Cambios de Correo usando GitHub y PuTTY

## 📋 Archivos a Subir

1. `funciones_correo_real.php`
2. `config_smtp.php`
3. `metadatos_formulario.php`
4. `ver_insignia_completa.php`
5. `probar_correo_tiempo_real.php`
6. `verificar_correos_enviados.php`

---

## 🔄 PASO 1: Subir Cambios a GitHub (Desde tu PC)

### 1.1. Abre PowerShell en tu máquina local

```powershell
cd C:\xampp\htdocs\Insignias_TecNM_Funcional
```

### 1.2. Ver qué archivos cambiaron

```powershell
git status
```

### 1.3. Agregar los archivos modificados

```powershell
git add funciones_correo_real.php
git add config_smtp.php
git add metadatos_formulario.php
git add ver_insignia_completa.php
git add probar_correo_tiempo_real.php
git add verificar_correos_enviados.php
```

O agregar todos de una vez:

```powershell
git add funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php
```

### 1.4. Hacer commit

```powershell
git commit -m "Fix: Sistema de correo en tiempo real - PHPMailer como método principal"
```

### 1.5. Subir a GitHub

```powershell
git push origin main
```

**Si te pide autenticación:**
- Usa tu **Personal Access Token** de GitHub
- O configura GitHub Desktop si prefieres

---

## 📥 PASO 2: Actualizar en el Servidor (Usando PuTTY)

### 2.1. Conectarte al servidor con PuTTY

1. Abre **PuTTY**
2. En **Host Name (or IP address)**, escribe: `158.23.160.163`
3. En **Port**: `22`
4. En **Connection type**: `SSH`
5. En **Saved Sessions**, puedes guardar como: `InsigniasTecNM`
6. Click en **Open**

### 2.2. Autenticarte

- **Login as**: `devusr01`
- Si te pide contraseña, ingrésala
- O si usas clave SSH, configúrala en PuTTY (Connection > SSH > Auth > Credentials)

### 2.3. Navegar al directorio del proyecto

```bash
cd /var/www/html/Insignias_TecNM_Funcional
```

**O si está en otra ubicación:**

```bash
cd /var/www/Insignias_TecNM_Funcional
```

### 2.4. Verificar que estás en el directorio correcto

```bash
pwd
ls -la | head -20
```

### 2.5. Verificar estado de Git

```bash
git status
```

### 2.6. Actualizar desde GitHub

```bash
sudo git pull origin main
```

**Si te pide autenticación de GitHub:**
- Puede que necesites configurar credenciales
- O usar un token de acceso

### 2.7. Verificar que se actualizaron los archivos

```bash
git log --oneline -1
ls -la funciones_correo_real.php config_smtp.php
```

### 2.8. Ajustar permisos

```bash
sudo chown www-data:www-data funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php

sudo chmod 644 funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php
```

### 2.9. Verificar permisos

```bash
ls -la funciones_correo_real.php config_smtp.php
```

Deberías ver algo como:
```
-rw-r--r-- 1 www-data www-data 12345 funciones_correo_real.php
-rw-r--r-- 1 www-data www-data  1234 config_smtp.php
```

---

## ✅ PASO 3: Verificar que Funcionó

### 3.1. Probar el correo en tiempo real

Abre en tu navegador:
```
http://158.23.160.163/probar_correo_tiempo_real.php
```

### 3.2. Verificar logs (si hay problemas)

En PuTTY, ejecuta:
```bash
tail -n 50 /var/log/apache2/error.log | grep -i correo
```

---

## ⚠️ Si hay Problemas

### Problema 1: Git pide autenticación

**Solución:**
```bash
# Configurar credenciales de GitHub
git config --global user.name "TuNombre"
git config --global user.email "tu@email.com"

# O usar token de acceso
git remote set-url origin https://TU_TOKEN@github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git
```

### Problema 2: "Permission denied" al hacer git pull

**Solución:**
```bash
# Usar sudo
sudo git pull origin main

# O cambiar propietario del directorio .git
sudo chown -R devusr01:devusr01 .git
```

### Problema 3: Archivos no se actualizaron

**Solución:**
```bash
# Forzar actualización
sudo git fetch origin
sudo git reset --hard origin/main
```

### Problema 4: Conflicto de cambios locales

**Solución:**
```bash
# Guardar cambios locales
git stash

# Actualizar
sudo git pull origin main

# Si necesitas recuperar cambios locales
git stash pop
```

---

## 📝 Resumen de Comandos (Copia y Pega)

### En PowerShell (tu PC):
```powershell
cd C:\xampp\htdocs\Insignias_TecNM_Funcional
git add funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php
git commit -m "Fix: Sistema de correo en tiempo real"
git push origin main
```

### En PuTTY (servidor):
```bash
cd /var/www/html/Insignias_TecNM_Funcional
sudo git pull origin main
sudo chown www-data:www-data funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php
sudo chmod 644 funciones_correo_real.php config_smtp.php metadatos_formulario.php ver_insignia_completa.php probar_correo_tiempo_real.php verificar_correos_enviados.php
ls -la funciones_correo_real.php config_smtp.php
```

---

## 🎯 Listo!

Después de estos pasos, el sistema de correo en tiempo real estará funcionando en el servidor.

