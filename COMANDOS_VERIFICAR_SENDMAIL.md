# 🔍 Comandos para Verificar Sendmail en el Servidor

Ejecuta estos comandos en PuTTY para verificar si sendmail está instalado y configurado:

## 1. Verificar si sendmail está instalado

```bash
# Buscar sendmail en el sistema
which sendmail

# O buscar en ubicaciones comunes
whereis sendmail

# Verificar si el servicio sendmail está corriendo
systemctl status sendmail

# O si usas postfix (alternativa común)
systemctl status postfix
```

## 2. Verificar configuración de PHP

```bash
# Ver configuración de sendmail_path en PHP
php -i | grep sendmail_path

# Ver toda la configuración de mail
php -i | grep -i mail

# Ver ubicación de php.ini
php --ini
```

## 3. Verificar si mail() funciona

```bash
# Crear un script de prueba rápido
cat > /tmp/test_mail.php << 'EOF'
<?php
$resultado = mail('test@example.com', 'Prueba', 'Mensaje de prueba');
if ($resultado) {
    echo "✅ mail() devolvió true\n";
} else {
    echo "❌ mail() devolvió false\n";
}
echo "Error: " . error_get_last()['message'] . "\n";
EOF

# Ejecutar el script
php /tmp/test_mail.php
```

## 4. Verificar logs de sendmail

```bash
# Ver logs de sendmail (si existe)
tail -n 20 /var/log/mail.log

# O logs de postfix
tail -n 20 /var/log/mail.log

# Ver logs de Apache/PHP
tail -n 50 /var/log/apache2/error.log | grep -i mail
```

## 5. Verificar permisos

```bash
# Ver permisos del ejecutable sendmail
ls -la $(which sendmail 2>/dev/null)

# Ver si el usuario www-data puede ejecutar sendmail
sudo -u www-data which sendmail
```

## 6. Instalar sendmail (si no está instalado)

```bash
# En Ubuntu/Debian
sudo apt-get update
sudo apt-get install sendmail

# En CentOS/RHEL
sudo yum install sendmail

# Configurar sendmail
sudo sendmailconfig
```

## 7. Verificar configuración de sendmail

```bash
# Ver configuración de sendmail
cat /etc/mail/sendmail.cf | grep -i "DS\|SMART_HOST"

# Ver si hay configuración de relay
cat /etc/mail/sendmail.mc | grep -i "SMART_HOST"
```

## Resultado Esperado

### ✅ Si sendmail está configurado correctamente:
- `which sendmail` debería mostrar: `/usr/sbin/sendmail` o similar
- `php -i | grep sendmail_path` debería mostrar: `sendmail_path => /usr/sbin/sendmail`
- `systemctl status sendmail` debería mostrar: `active (running)`

### ⚠️ Si sendmail NO está instalado:
- `which sendmail` no devuelve nada
- `systemctl status sendmail` muestra: `Unit sendmail.service could not be found`
- Necesitarás instalar sendmail o usar PHPMailer con SMTP

## Alternativa: Usar PHPMailer

Si sendmail no está disponible, el sistema automáticamente usará PHPMailer con las credenciales en `config_smtp.php`.

Para verificar que PHPMailer funciona:
```bash
# En el navegador, abre:
http://158.23.160.163/Insignias_TecNM_Funcional/probar_correo.php
```

