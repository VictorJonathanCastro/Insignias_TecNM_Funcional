#!/bin/bash
# Script para diagnosticar por qué no se guardan los INSERTs

echo "=== DIAGNÓSTICO DE INSERT EN insigniasotorgadas ==="
echo ""

# 1. Verificar permisos del usuario MySQL
echo "1. PERMISOS DEL USUARIO MySQL:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SHOW GRANTS FOR 'insignia_user'@'localhost';"
echo ""

# 2. Verificar si hay errores en los logs de PHP
echo "2. ÚLTIMOS ERRORES DE PHP (últimas 20 líneas):"
tail -n 20 /var/log/apache2/error.log 2>/dev/null || tail -n 20 /var/log/php_errors.log 2>/dev/null || echo "No se encontraron logs de PHP"
echo ""

# 3. Verificar logs de PHP específicos del proyecto
echo "3. LOGS DE PHP DEL PROYECTO (si existen):"
if [ -f "/var/www/html/php_errors.log" ]; then
    tail -n 30 /var/www/html/php_errors.log | grep -i "insert\|insigniasotorgadas\|error" || echo "No hay errores relacionados con INSERT"
else
    echo "No existe php_errors.log en el proyecto"
fi
echo ""

# 4. Verificar estructura de la tabla
echo "4. ESTRUCTURA DE insigniasotorgadas:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "DESCRIBE insigniasotorgadas;"
echo ""

# 5. Intentar un INSERT de prueba manual
echo "5. INTENTAR INSERT DE PRUEBA:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "INSERT INTO insigniasotorgadas (Codigo_Insignia, Destinatario, Periodo_Emision, Responsable_Emision, Estatus, Fecha_Emision, Fecha_Vencimiento) VALUES ('TEST-001', 11, 1, 1, 1, '2025-01-01', '2025-12-31');"
echo ""

# 6. Verificar si el INSERT de prueba funcionó
echo "6. VERIFICAR SI EL INSERT DE PRUEBA FUNCIONÓ:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM insigniasotorgadas WHERE Codigo_Insignia = 'TEST-001';"
echo ""

# 7. Verificar autocommit
echo "7. VERIFICAR CONFIGURACIÓN DE AUTOCOMMIT:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT @@autocommit;"
echo ""

# 8. Verificar si hay transacciones activas
echo "8. VERIFICAR TRANSACCIONES ACTIVAS:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SHOW PROCESSLIST;" | head -n 10
echo ""

# 9. Verificar última fecha de modificación de la tabla
echo "9. INFORMACIÓN DE LA TABLA:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT TABLE_NAME, CREATE_TIME, UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'insignia' AND TABLE_NAME = 'insigniasotorgadas';"
echo ""

echo "=== FIN DE DIAGNÓSTICO ==="

