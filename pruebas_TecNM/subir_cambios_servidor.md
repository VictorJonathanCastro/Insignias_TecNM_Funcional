# 🚀 Guía Rápida: Subir Cambios al Servidor Remoto

## ✅ Cambios Realizados (Solo en tu máquina local)

Se corrigió el archivo `modulo_de_administracion.php` para que funcione correctamente en Ubuntu.

---

## 📤 Opción 1: Subir Solo el Archivo Modificado (MÁS RÁPIDO)

### Paso 1: Subir el archivo usando SCP desde PowerShell

```powershell
# Navegar a la carpeta del proyecto
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

# Subir SOLO el archivo modificado
scp -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" modulo_de_administracion.php devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

**Si el dominio no funciona, usa la IP del servidor:**
```powershell
scp -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" modulo_de_administracion.php devusr01@158.23.160.163:/var/www/Insignias_TecNM_Funcional/
```

### Paso 2: Verificar permisos en el servidor

Conéctate al servidor y verifica permisos:

```bash
# Conectarse al servidor
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@InsigniasTecNM

# O con IP:
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@158.23.160.163

# Una vez conectado, verificar permisos
cd /var/www/Insignias_TecNM_Funcional
ls -la modulo_de_administracion.php

# Si es necesario, ajustar permisos
sudo chown www-data:www-data modulo_de_administracion.php
sudo chmod 644 modulo_de_administracion.php
```

---

## 📤 Opción 2: Usar GitHub (Recomendado para actualizaciones futuras)

### Paso 1: Subir cambios a GitHub

```powershell
# Navegar a la carpeta del proyecto
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

# Ver qué archivos cambiaron
git status

# Agregar el archivo modificado
git add modulo_de_administracion.php

# Hacer commit
git commit -m "Fix: Corregir error HTTP 500 en modulo_de_administracion.php para Ubuntu"

# Subir a GitHub
git push origin main
```

### Paso 2: Actualizar en el servidor

```bash
# Conectarse al servidor
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@InsigniasTecNM

# Ir al directorio del proyecto
cd /var/www/Insignias_TecNM_Funcional

# Actualizar desde GitHub
sudo git pull origin main
```

---

## 📤 Opción 3: Usar PuTTY/PSCP (Interfaz Gráfica)

### Paso 1: Abrir PSCP desde PowerShell

```powershell
# Navegar a la carpeta de PuTTY (o usar ruta completa)
cd "C:\Program Files\PuTTY"

# Subir archivo usando PSCP
.\pscp.exe -i "C:\Users\vc556\Desktop\llaves\priv_insignias.ppk" C:\xampp\htdocs\Insignias_TecNM_Funcional\modulo_de_administracion.php devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

---

## ✅ Verificar que Funcionó

1. **Abre tu navegador** y ve a: `http://158.23.160.163/modulo_de_administracion.php`
2. **Debería cargar correctamente** sin error HTTP 500
3. **Si aún hay error**, revisa los logs:
   ```bash
   # En el servidor
   tail -f /var/www/Insignias_TecNM_Funcional/php_errors.log
   ```

---

## 🔧 Si Necesitas Ver los Logs de Error

```bash
# Conectarse al servidor
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@InsigniasTecNM

# Ver los últimos errores
tail -50 /var/www/Insignias_TecNM_Funcional/php_errors.log

# O ver errores en tiempo real
tail -f /var/www/Insignias_TecNM_Funcional/php_errors.log
```

---

## ⚠️ Nota Importante

- El archivo `conexion.php` NO se debe subir (está en .gitignore) porque tiene credenciales específicas del servidor
- Solo sube `modulo_de_administracion.php` que es el archivo que se corrigió
- Los cambios ya están aplicados en tu máquina local, solo necesitas subirlos al servidor

