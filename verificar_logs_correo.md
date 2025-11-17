# 🔍 Verificar Logs para Ver Por Qué No Funciona PHPMailer

## Comando para ver los logs

Ejecuta en PuTTY:

```bash
# Ver los últimos errores relacionados con correo
sudo tail -n 100 /var/log/apache2/error.log | grep -i "correo\|smtp\|mail\|phpmailer"

# O ver todos los errores recientes
sudo tail -n 50 /var/log/apache2/error.log
```

## Qué buscar en los logs

### Si PHPMailer falló, verás mensajes como:
- `❌ PHPMailer: No hay credenciales SMTP configuradas`
- `❌ Todos los servidores SMTP fallaron`
- `Error de autenticación`
- `Error con servidor smtp-mail.outlook.com`

### Si PHPMailer funcionó, verás:
- `✅ Correo PHPMailer enviado exitosamente (TIEMPO REAL)`
- `✅ Correo enviado exitosamente usando servidor: smtp-mail.outlook.com`

## Posibles problemas

1. **Credenciales incorrectas**: Office 365 rechaza la autenticación
2. **Contraseña de aplicación requerida**: Si tienes 2FA, necesitas contraseña de aplicación
3. **Servidor SMTP bloqueado**: Firewall bloqueando puerto 587
4. **Error de conexión**: No puede conectarse a Office 365

