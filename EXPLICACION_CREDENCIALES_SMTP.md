# ¿Por qué necesitas credenciales SMTP para enviar notificaciones?

## Respuesta corta: **NO siempre las necesitas**

El sistema intenta enviar correos en este orden:

## 1️⃣ PRIMERO: Intenta SIN credenciales (para servidores de TecNM)

El código **primero intenta enviar sin credenciales** a los servidores de TecNM:
- `smtp.tecnm.mx`
- `mail.tecnm.mx`  
- `smtp.smarcos.tecnm.mx`

**Si el servidor de TecNM permite enviar sin autenticación, funcionará sin credenciales.**

## 2️⃣ SEGUNDO: Si falla, intenta CON credenciales

Solo si el servidor rechaza el envío sin autenticación, entonces intenta con credenciales.

## 3️⃣ TERCERO: mail() nativo (sin credenciales)

Si PHPMailer falla, usa `mail()` nativo de PHP, que puede funcionar sin credenciales si:
- El servidor tiene `sendmail` configurado
- O el servidor permite envío local

## 4️⃣ ÚLTIMO RECURSO: Simulación

Solo si TODO lo anterior falla, guarda el correo en un archivo (simulación).

---

## ¿Cuándo SÍ necesitas credenciales?

Solo necesitas configurar credenciales SMTP si:

1. **El servidor de TecNM requiere autenticación**
   - Algunos servidores SMTP modernos requieren autenticación por seguridad
   - Para evitar spam y abuso

2. **Quieres usar Office 365 u otro servidor externo**
   - Office 365, Gmail, etc. SIEMPRE requieren credenciales
   - Son más confiables pero necesitan autenticación

3. **mail() nativo no funciona en tu servidor**
   - Si tu servidor no tiene sendmail configurado
   - O si el servidor bloquea envíos sin autenticación

---

## ¿Cómo saber si necesitas credenciales?

### Prueba 1: Verifica si funciona sin credenciales

1. Deja `SMTP_PASSWORD = 'CAMBIAR_POR_CONTRASEÑA_REAL'` en `config_smtp.php`
2. Envía un formulario
3. Revisa los logs de error:
   ```bash
   tail -n 50 /var/log/apache2/error.log | grep -i 'correo\|smtp'
   ```

**Si ves:**
- ✅ `"Correo NATIVO (usando PHPMailer internamente) enviado en TIEMPO REAL"` → **NO necesitas credenciales**
- ✅ `"Correo NATIVO enviado"` → **NO necesitas credenciales** (pero puede tardar)
- ❌ `"Todos los métodos fallaron, usando simulación"` → **SÍ necesitas credenciales**

### Prueba 2: Usa el script de diagnóstico

Ejecuta `diagnosticar_envio_formulario.php` en tu navegador y verás exactamente qué método se está usando.

---

## Resumen

- **NO siempre necesitas credenciales** - El código intenta primero sin ellas
- **Solo necesitas credenciales si** el servidor las requiere o quieres usar Office 365/Gmail
- **Si funciona sin credenciales**, déjalas como están y funcionará
- **Si NO funciona**, entonces sí necesitas configurar credenciales

---

## Solución rápida

1. **Primero prueba sin cambiar nada** - Puede que ya funcione
2. **Si no funciona**, revisa los logs para ver el error específico
3. **Solo si el error dice "authentication required"**, entonces configura credenciales

