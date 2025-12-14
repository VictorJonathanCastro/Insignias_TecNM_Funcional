# 📧 INSTRUCCIONES PARA CONFIGURAR EL CORREO

## ⚠️ IMPORTANTE: Credenciales del Sistema, NO del Estudiante

Las credenciales en `config_smtp.php` son del **SISTEMA** que envía los correos, **NO** de los estudiantes.

### ¿Qué correo usar?

Usa un correo institucional de TecNM que tenga permisos para enviar correos, por ejemplo:
- `sistema.insignias@smarcos.tecnm.mx`
- `noreply@smarcos.tecnm.mx`
- `211230001@smarcos.tecnm.mx` (si tienes permisos)
- Cualquier correo institucional de TecNM con permisos SMTP

### ¿Qué contraseña usar?

La **contraseña REAL** del correo que uses arriba.

## 📝 Pasos para Configurar

### 1. Editar `config_smtp.php`:

```php
// Correo del SISTEMA
define('SMTP_USERNAME', 'TU_CORREO_INSTITUCIONAL@smarcos.tecnm.mx');
define('SMTP_PASSWORD', 'TU_CONTRASEÑA_REAL'); // ⚠️ Contraseña real del correo
```

### 2. Probar el correo:

Abre en el navegador:
```
http://158.23.160.163/probar_correo.php?correo=TU_CORREO@smarcos.tecnm.mx
```

### 3. Si usas Office 365:

- Servidor: `smtp.office365.com`
- Puerto: `587`
- Encriptación: `tls`
- Usa tu correo y contraseña de Office 365

### 4. Si usas Gmail:

- Servidor: `smtp.gmail.com`
- Puerto: `587`
- Encriptación: `tls`
- Si tienes 2FA, usa una **Contraseña de Aplicación** (no tu contraseña normal)

## ✅ Resultado Esperado

Cuando registres una insignia:
1. El sistema envía el correo desde: `TU_CORREO_INSTITUCIONAL@smarcos.tecnm.mx`
2. El estudiante recibe en: `estudiante@ejemplo.com` (solo necesita su correo)
3. El correo incluye:
   - Imagen de la insignia (clic/doble clic para ver certificado)
   - Datos de la insignia
   - Link a verificación pública

## 🔍 Verificar si Funciona

Después de probar, verifica los logs:
```bash
tail -n 50 /var/log/apache2/error.log | grep -i "correo\|smtp"
```

Si ves `✅ Correo enviado exitosamente`, ¡funciona!

