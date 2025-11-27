# ⚡ Solución: Correo en Tiempo Real

## ✅ Cambios Realizados

### 1. Prioridad de Servidores SMTP Cambiada
- **ANTES:** Probaba Office 365 primero
- **AHORA:** Prueba primero servidores de TecNM (más confiables)

### 2. Configuración Mejorada
- Mejor manejo de autenticación
- Configuración SSL optimizada para diferentes servidores
- Mejores mensajes de error

### 3. Orden de Prueba de Servidores
1. `smtp.tecnm.mx` (TecNM - RECOMENDADO)
2. `mail.tecnm.mx` (TecNM alternativo)
3. `smtp.smarcos.tecnm.mx` (TecNM específico)
4. `smtp-mail.outlook.com` (Office 365)
5. `smtp.office365.com` (Office 365 alternativo)

---

## 📤 Subir Cambios a GitHub

```powershell
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

git add funciones_correo_real.php config_smtp.php

git commit -m "Fix: Correo tiempo real - Prioridad servidores TecNM"

git push origin main
```

---

## 📥 Actualizar en Servidor (PuTTY)

```bash
cd /var/www/html/Insignias_TecNM_Funcional

sudo git pull origin main

sudo chown www-data:www-data funciones_correo_real.php config_smtp.php

sudo chmod 644 funciones_correo_real.php config_smtp.php
```

---

## 🧪 Probar el Correo

Abre en el navegador:
```
http://158.23.160.163/probar_correo_tiempo_real.php
```

---

## ⚠️ Si Office 365 Sigue Fallando

Si los servidores de TecNM no funcionan y Office 365 sigue dando error de autenticación, necesitas una **Contraseña de Aplicación**:

### Pasos para Generar Contraseña de Aplicación:

1. Ve a: https://account.microsoft.com/security
2. Inicia sesión con: `sistema.insignias@smarcos.tecnm.mx`
3. Ve a **Seguridad** → **Verificación en dos pasos**
4. Busca **Contraseñas de aplicaciones**
5. Crea una nueva contraseña para "Sistema Insignias"
6. Copia la contraseña generada (16 caracteres)
7. Actualiza en `config_smtp.php`:

```php
define('SMTP_PASSWORD', 'LA_CONTRASEÑA_DE_APLICACIÓN_QUE_TE_DEN'); // Contraseña de aplicación (16 caracteres)
```

---

## ✅ Resultado Esperado

Después de actualizar, cuando pruebes `probar_correo_tiempo_real.php`:

- ✅ Si funciona con servidor TecNM: **"⚡ CORREO ENVIADO EN TIEMPO REAL"**
- ⚠️ Si solo funciona mail() nativo: **"⚠️ CORREO ENVIADO (puede tener retrasos)"**

---

## 🔍 Verificar Logs

Si hay problemas, revisa los logs:

```bash
tail -n 50 /var/log/apache2/error.log | grep -i "correo\|smtp"
```

---

## 📝 Nota Importante

- Los servidores de TecNM (`smtp.tecnm.mx`, `mail.tecnm.mx`) son los más probables de funcionar
- Office 365 puede requerir contraseña de aplicación si tiene 2FA habilitado
- El sistema probará todos los servidores automáticamente hasta encontrar uno que funcione

