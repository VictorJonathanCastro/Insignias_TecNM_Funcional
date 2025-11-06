#!/bin/bash
# Script para configurar el usuario insignia_user en MySQL
# Ejecutar con: sudo bash configurar_usuario_mysql.sh

echo "Configurando usuario insignia_user en MySQL..."

# Establecer la contraseña del usuario (usando comillas simples para evitar problemas con !)
sudo mysql -u root <<EOF
ALTER USER 'insignia_user'@'localhost' IDENTIFIED BY 'InsigniaTecNM2024!';
GRANT ALL PRIVILEGES ON insignia.* TO 'insignia_user'@'localhost';
FLUSH PRIVILEGES;
SELECT user, host FROM mysql.user WHERE user = 'insignia_user';
EOF

echo "Usuario configurado correctamente!"

