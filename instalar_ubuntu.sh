#!/bin/bash
# ========================================
# SCRIPT DE INSTALACIÓN AUTOMÁTICA - UBUNTU SERVER 22.04
# Sistema de Insignias TecNM
# ========================================

set -e  # Salir si hay error

echo "🚀 Iniciando instalación del Sistema de Insignias TecNM en Ubuntu Server 22.04"
echo "================================================================================"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir mensajes con colores
print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_info() { echo -e "${YELLOW}ℹ️  $1${NC}"; }
print_step() { echo -e "\n${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; echo -e "${GREEN}📋 $1${NC}"; echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"; }

# Verificar que se ejecuta con sudo
if [ "$EUID" -ne 0 ]; then 
    print_error "Por favor ejecuta este script con sudo"
    echo "Uso: sudo ./instalar_ubuntu.sh"
    exit 1
fi

# Paso 1: Actualizar sistema
print_step "Paso 1: Actualizando sistema"
apt update -y
apt upgrade -y
print_success "Sistema actualizado"

# Paso 2: Instalar Apache
print_step "Paso 2: Instalando Apache"
apt install apache2 -y
systemctl enable apache2
systemctl start apache2
print_success "Apache instalado y funcionando"

# Paso 3: Instalar PHP 8.1+
print_step "Paso 3: Instalando PHP 8.1"
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update -y
apt install php8.1 php8.1-cli php8.1-common php8.1-mysql \
            php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl \
            php8.1-xml php8.1-bcmath libapache2-mod-php8.1 -y
print_success "PHP 8.1 instalado"

# Verificar versión de PHP
PHP_VERSION=$(php -v | head -n 1)
print_info "Versión de PHP: $PHP_VERSION"

# Paso 4: Instalar MySQL
print_step "Paso 4: Instalando MySQL"
apt install mysql-server -y
systemctl enable mysql
systemctl start mysql
print_success "MySQL instalado"

# Configurar MySQL de forma segura
print_info "Configurando MySQL (esto puede tomar unos minutos)..."
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root123';"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

# Crear base de datos y usuario
mysql -uroot -proot123 <<EOF
CREATE DATABASE IF NOT EXISTS insignia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'tecnm_user'@'localhost' IDENTIFIED BY 'tecnm_pass123';
GRANT ALL PRIVILEGES ON insignia.* TO 'tecnm_user'@'localhost';
FLUSH PRIVILEGES;
EOF

print_success "Base de datos 'insignia' creada"
print_info "Usuario de BD: tecnm_user"
print_info "Contraseña de BD: tecnm_pass123"
print_info "⚠️  IMPORTANTE: Cambiar estas credenciales después"

# Paso 5: Instalar Composer
print_step "Paso 5: Instalando Composer"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_success "Composer instalado"
else
    print_info "Composer ya está instalado"
fi

# Paso 6: Configurar directorio del proyecto
print_step "Paso 6: Configurando directorio del proyecto"
PROJECT_DIR="/var/www/Insignias_TecNM_Funcional"

# Verificar si el directorio existe
if [ ! -d "$PROJECT_DIR" ]; then
    print_error "El directorio $PROJECT_DIR no existe"
    print_info "Por favor transfiere los archivos del proyecto a $PROJECT_DIR antes de continuar"
    print_info "Puedes usar: scp -r proyecto usuario@servidor:/var/www/"
    exit 1
fi

# Configurar permisos
chown -R www-data:www-data "$PROJECT_DIR"
find "$PROJECT_DIR" -type f -exec chmod 644 {} \;
find "$PROJECT_DIR" -type d -exec chmod 755 {} \;

# Permisos especiales para directorios específicos
if [ -d "$PROJECT_DIR/imagen" ]; then
    chmod 775 "$PROJECT_DIR/imagen"
fi

if [ -d "$PROJECT_DIR/firmas_digitales" ]; then
    chmod 775 "$PROJECT_DIR/firmas_digitales"
fi

# Crear directorios necesarios
mkdir -p "$PROJECT_DIR/uploads"
mkdir -p "$PROJECT_DIR/logs"
chmod 775 "$PROJECT_DIR/uploads"
chmod 775 "$PROJECT_DIR/logs"

print_success "Permisos configurados"

# Paso 7: Instalar dependencias de Composer
print_step "Paso 7: Instalando dependencias de Composer"
cd "$PROJECT_DIR"
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --quiet
    print_success "Dependencias instaladas"
else
    print_info "No se encontró composer.json, omitiendo este paso"
fi

# Paso 8: Configurar conexión.php
print_step "Paso 8: Configurando archivo de conexión"
CONNECTION_FILE="$PROJECT_DIR/conexion.php"

if [ -f "$CONNECTION_FILE" ]; then
    # Crear backup
    cp "$CONNECTION_FILE" "$CONNECTION_FILE.backup"
    
    # Configurar conexión para Ubuntu
    cat > "$CONNECTION_FILE" <<'EOF'
<?php
$servidor = "localhost";
$usuario = "tecnm_user";
$password = "tecnm_pass123";
$bd = "insignia";
$puerto = 3306;

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $bd, $puerto);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
EOF
    print_success "Archivo conexion.php configurado"
    print_info "⚠️  CREDENCIALES: cambia tecnm_pass123 por una contraseña segura"
else
    print_error "No se encontró el archivo conexion.php"
fi

# Paso 9: Configurar Apache
print_step "Paso 9: Configurando Apache"

# Crear archivo de configuración
cat > /etc/apache2/sites-available/insignias.conf <<EOF
<VirtualHost *:80>
    ServerAdmin admin@tecnm.mx
    ServerName localhost
    
    DocumentRoot $PROJECT_DIR
    
    <Directory $PROJECT_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/insignias_error.log
    CustomLog \${APACHE_LOG_DIR}/insignias_access.log combined
</VirtualHost>
EOF

# Habilitar sitio y módulos
a2ensite insignias.conf
a2dissite 000-default.conf
a2enmod rewrite
systemctl restart apache2

print_success "Apache configurado"

# Paso 10: Configurar Firewall
print_step "Paso 10: Configurando Firewall"
if command -v ufw &> /dev/null; then
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
    print_success "Firewall configurado"
else
    print_info "UFW no está instalado, omitiendo este paso"
fi

# Paso 11: Importar base de datos (si existe)
print_step "Paso 11: Importando base de datos"
DB_SQL="$PROJECT_DIR/BD/backup_sistema_funcional.sql"

if [ -f "$DB_SQL" ]; then
    print_info "Importando desde backup_sistema_funcional.sql..."
    mysql -uroot -proot123 insignia < "$DB_SQL"
    print_success "Base de datos importada"
elif [ -f "$PROJECT_DIR/BD/estructura_completa_con_metadatos.sql" ]; then
    print_info "Importando desde estructura_completa_con_metadatos.sql..."
    mysql -uroot -proot123 insignia < "$PROJECT_DIR/BD/estructura_completa_con_metadatos.sql"
    print_success "Base de datos importada"
else
    print_info "No se encontró archivo SQL de base de datos"
    print_info "Puedes importarlo manualmente después con: mysql -u root -p insignia < archivo.sql"
fi

# Paso 12: Mostrar información final
print_step "Instalación Completada"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Sistema instalado exitosamente"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
print_info "🌐 Acceso al sistema:"
echo "   - URL: http://localhost/ (o tu IP pública)"
echo "   - Login: http://localhost/login.php"
echo ""
print_info "🔐 Credenciales por defecto:"
echo "   - Email: admin@tecnm.mx"
echo "   - Contraseña: admin123"
echo ""
print_info "🗄️  Base de datos:"
echo "   - Usuario: tecnm_user"
echo "   - Contraseña: tecnm_pass123"
echo "   - Nombre BD: insignia"
echo ""
print_info "⚠️  IMPORTANTE - Acciones pendientes:"
echo "   1. Cambiar contraseña de usuario admin"
echo "   2. Cambiar contraseña de base de datos (tecnm_pass123)"
echo "   3. Configurar dominio/IP pública en apache"
echo "   4. Configurar SSL/HTTPS para producción"
echo ""
print_info "📁 Directorio del proyecto:"
echo "   $PROJECT_DIR"
echo ""
print_info "📝 Logs:"
echo "   - Apache Error: /var/log/apache2/insignias_error.log"
echo "   - Apache Access: /var/log/apache2/insignias_access.log"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎉 ¡Instalación completada exitosamente!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Siguiente paso: Acceder a http://tu-ip/login.php"
echo ""
echo "¡Excelente tarde equipo! 🎓"
echo ""

