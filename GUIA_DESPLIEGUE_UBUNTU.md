# 🚀 Guía de Despliegue - Ubuntu Server 22.04

## 📋 Resumen

Esta guía te ayudará a desplegar el Sistema de Insignias TecNM en una máquina virtual Ubuntu Server 22.04.

---

## ✅ Requisitos Previos

- **Ubuntu Server 22.04** instalada en MV
- **Acceso SSH** a la máquina virtual
- **IP pública** o configuración de red accesible
- **Credenciales** de acceso a la MV

---

## 🎯 Paso 1: Acceder a la Máquina Virtual

### Conectarse vía SSH:

```bash
ssh -i priv_insignias devusr01@InsigniasTecNM
# O usando IP directamente:
ssh -i priv_insignias devusr01@ip_servidor
# Ejemplo: ssh -i priv_insignias devusr01@192.168.1.100
```

---

## 🔧 Paso 2: Actualizar el Sistema

```bash
# Actualizar lista de paquetes
sudo apt update

# Actualizar sistema
sudo apt upgrade -y

# Reiniciar si es necesario
sudo reboot
```

---

## 📦 Paso 3: Instalar Servidor Web (Apache)

```bash
# Instalar Apache
sudo apt install apache2 -y

# Verificar estado
sudo systemctl status apache2

# Habilitar Apache en el arranque
sudo systemctl enable apache2
```

---

## 🐘 Paso 4: Instalar PHP 8.1+

```bash
# Agregar repositorio de PHP
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP y extensiones necesarias
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mysql \
                 php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl \
                 php8.1-xml php8.1-bcmath libapache2-mod-php8.1 -y

# Verificar instalación
php -v
```

---

## 🗄️ Paso 5: Instalar MySQL/MariaDB

```bash
# Instalar MySQL Server
sudo apt install mysql-server -y

# Configurar MySQL de forma segura
sudo mysql_secure_installation

# Acceder a MySQL
sudo mysql -u root -p
```

### Configurar usuario de base de datos:

```sql
-- Crear usuario para la aplicación
CREATE USER 'tecnm_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';

-- Crear base de datos
CREATE DATABASE insignia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Otorgar permisos
GRANT ALL PRIVILEGES ON insignia.* TO 'tecnm_user'@'localhost';
FLUSH PRIVILEGES;

-- Salir
EXIT;
```

---

## 📥 Paso 6: Transferir Archivos del Sistema

### Opción A: Usando SCP (desde tu máquina local)

```bash
# Desde tu computadora Windows/Mac/Linux
scp -i priv_insignias -r Insignias_TecNM_Funcional devusr01@InsigniasTecNM:/var/www/
# O usando IP directamente:
scp -i priv_insignias -r Insignias_TecNM_Funcional devusr01@ip_servidor:/var/www/
```

### Opción B: Usando Git (si tienes repositorio)

```bash
# En el servidor
cd /var/www
sudo git clone https://tu-repositorio/Insignias_TecNM_Funcional.git
```

### Opción C: Usando SFTP (FileZilla o similar)

1. Conectar con FileZilla usando SFTP
2. Subir la carpeta `Insignias_TecNM_Funcional` a `/var/www/`

### Configurar permisos:

```bash
# Cambiar propietario
sudo chown -R www-data:www-data /var/www/Insignias_TecNM_Funcional

# Dar permisos correctos
cd /var/www/Insignias_TecNM_Funcional
sudo chmod 755 .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Permisos especiales para directorios específicos
sudo chmod 775 imagen/
sudo chmod 775 firmas_digitales/
sudo mkdir -p uploads logs
sudo chmod 775 uploads logs
```

---

## ⚙️ Paso 7: Configurar Base de Datos

### Importar estructura de base de datos:

```bash
# Ir al directorio del proyecto
cd /var/www/Insignias_TecNM_Funcional

# Importar estructura SQL
sudo mysql -u tecnm_user -p insignia < BD/backup_sistema_funcional.sql

# O usar el archivo que tengas
sudo mysql -u tecnm_user -p insignia < BD/estructura_completa_con_metadatos.sql
```

---

## 🔐 Paso 8: Configurar Conexión

```bash
# Editar archivo de conexión
sudo nano conexion.php
```

### Configurar con tus credenciales:

```php
<?php
// Configuración para Ubuntu Server
$servidor = "localhost";        // o "127.0.0.1"
$usuario = "tecnm_user";        // Usuario que creaste
$password = "tu_password_seguro"; // Contraseña del usuario
$bd = "insignia";               // Nombre de la base de datos
$puerto = 3306;                 // Puerto de MySQL

// Resto del código...
?>
```

Guardar: `Ctrl + X`, luego `Y`, luego `Enter`

---

## 🌐 Paso 9: Configurar Apache

### Crear archivo de configuración:

```bash
sudo nano /etc/apache2/sites-available/insignias.conf
```

### Agregar configuración:

```apache
<VirtualHost *:80>
    ServerAdmin admin@tecnm.mx
    ServerName InsigniasTecNM
    ServerAlias www.InsigniasTecNM
    # Si no tienes dominio configurado aún, usa: ServerName tu_ip_publica
    
    DocumentRoot /var/www/Insignias_TecNM_Funcional
    
    <Directory /var/www/Insignias_TecNM_Funcional>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/insignias_error.log
    CustomLog ${APACHE_LOG_DIR}/insignias_access.log combined
</VirtualHost>
```

### Habilitar sitio y módulos:

```bash
# Habilitar sitio
sudo a2ensite insignias.conf

# Deshabilitar sitio por defecto
sudo a2dissite 000-default.conf

# Habilitar mod_rewrite
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2

# Verificar configuración
sudo apache2ctl configtest
```

---

## 📦 Paso 10: Instalar Composer (si no está)

```bash
# Instalar Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php

# Mover composer a ruta global
sudo mv composer.phar /usr/local/bin/composer

# Dar permisos
sudo chmod +x /usr/local/bin/composer

# Verificar
composer --version
```

### Instalar dependencias del proyecto:

```bash
cd /var/www/Insignias_TecNM_Funcional
sudo composer install --no-dev --optimize-autoloader
```

---

## 🔒 Paso 11: Configurar Firewall

```bash
# Verificar estado del firewall
sudo ufw status

# Permitir SSH (IMPORTANTE hacer esto primero)
sudo ufw allow 22/tcp

# Permitir HTTP
sudo ufw allow 80/tcp

# Permitir HTTPS (opcional pero recomendado)
sudo ufw allow 443/tcp

# Habilitar firewall
sudo ufw enable

# Verificar reglas
sudo ufw status numbered
```

---

## ✅ Paso 12: Verificar Instalación

### Pruebas básicas:

```bash
# Verificar Apache
curl http://localhost

# Verificar PHP
curl http://localhost/info.php

# Verificar base de datos
sudo mysql -u tecnm_user -p insignia -e "SHOW TABLES;"
```

### Acceder desde navegador:

1. **Con dominio**: `http://InsigniasTecNM/` o `https://InsigniasTecNM/`
2. **Con IP**: `http://tu_ip_publica`
3. **Local**: `http://localhost`

---

## 🐛 Paso 13: Solución de Problemas

### Ver logs de Apache:

```bash
# Ver errores
sudo tail -f /var/log/apache2/insignias_error.log

# Ver accesos
sudo tail -f /var/log/apache2/insignias_access.log

# Ver logs de PHP
sudo tail -f /var/log/apache2/error.log
```

### Comandos útiles:

```bash
# Reiniciar Apache
sudo systemctl restart apache2

# Reiniciar MySQL
sudo systemctl restart mysql

# Ver estado de servicios
sudo systemctl status apache2
sudo systemctl status mysql

# Ver permisos de archivos
ls -la /var/www/Insignias_TecNM_Funcional/
```

---

## 🔐 Paso 14: Seguridad (IMPORTANTE)

### Cambiar contraseñas por defecto:

```bash
# Cambiar contraseña de MySQL root
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'nueva_password_segura';
FLUSH PRIVILEGES;
EXIT;
```

### Configurar SSL/HTTPS (Opcional pero Recomendado):

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtener certificado SSL
sudo certbot --apache -d InsigniasTecNM -d www.InsigniasTecNM
```

---

## 📊 Paso 15: Configuración de PHP (Ajustes Opcionales)

```bash
# Editar configuración de PHP
sudo nano /etc/php/8.1/apache2/php.ini
```

### Ajustes recomendados:

```ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
memory_limit = 256M
date.timezone = America/Mexico_City
```

### Reiniciar Apache después de cambios:

```bash
sudo systemctl restart apache2
```

---

## 🎉 Paso 16: Acceso al Sistema

### URLs principales:

- **Sistema**: `http://InsigniasTecNM/` o `https://InsigniasTecNM/`
- **Login**: `http://InsigniasTecNM/login.php`
- **Admin**: `http://InsigniasTecNM/modulo_de_administracion.php`

### Credenciales por defecto:

```
Correo: admin@tecnm.mx
Contraseña: admin123
```

**⚠️ IMPORTANTE: Cambiar estas credenciales después del primer acceso**

---

## 📝 Checklist Final

- [ ] Apache instalado y funcionando
- [ ] PHP 8.1+ instalado con extensiones necesarias
- [ ] MySQL instalado y configurado
- [ ] Base de datos creada e importada
- [ ] Archivos transferidos a `/var/www/`
- [ ] Permisos configurados correctamente
- [ ] `conexion.php` configurado con credenciales
- [ ] Apache configurado y sitio habilitado
- [ ] Firewall configurado
- [ ] Sistema accesible desde navegador
- [ ] Primera sesión de administrador exitosa

---

## 🆘 Soporte y Ayuda

### Comandos de diagnóstico:

```bash
# Ver información del sistema
php -m                    # Ver módulos PHP instalados
mysql --version           # Ver versión de MySQL
apache2 -v                # Ver versión de Apache
php -v                    # Ver versión de PHP

# Ver configuración de Apache
apache2ctl -S             # Ver sitios configurados

# Ver procesos en ejecución
sudo systemctl status apache2
sudo systemctl status mysql
```

---

## 📞 Contacto

Si tienes problemas durante la instalación:

- **Email**: d_vinculacion0402@tecnm.mx
- **Documentación**: Ver README.md

---

## 🎓 Próximos Pasos

1. **Cambiar credenciales** por defecto
2. **Configurar backup automático** de base de datos
3. **Monitorear logs** regularmente
4. **Actualizar sistema** periódicamente
5. **Configurar HTTPS** para mayor seguridad

---

**¡Instalación completada exitosamente! 🎉**

*Sistema desarrollado con ❤️ para el TecNM*

