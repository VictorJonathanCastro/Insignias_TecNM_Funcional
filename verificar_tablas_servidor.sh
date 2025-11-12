#!/bin/bash
# Script para verificar tablas y datos en el servidor

echo "=== VERIFICAR TABLAS EN LA BASE DE DATOS ==="
echo ""

# 1. Ver todas las tablas
echo "1. TABLAS EXISTENTES:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SHOW TABLES;"
echo ""

# 2. Ver estructura de insigniasotorgadas
echo "2. ESTRUCTURA DE insigniasotorgadas:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "DESCRIBE insigniasotorgadas;"
echo ""

# 3. Ver todos los datos de insigniasotorgadas
echo "3. DATOS EN insigniasotorgadas (últimos 10):"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM insigniasotorgadas ORDER BY ID_otorgada DESC LIMIT 10;"
echo ""

# 4. Contar registros en insigniasotorgadas
echo "4. TOTAL DE REGISTROS EN insigniasotorgadas:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT COUNT(*) as total FROM insigniasotorgadas;"
echo ""

# 5. Ver estructura de destinatario
echo "5. ESTRUCTURA DE destinatario:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "DESCRIBE destinatario;"
echo ""

# 6. Ver últimos destinatarios
echo "6. ÚLTIMOS DESTINATARIOS (últimos 5):"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM destinatario ORDER BY ID_destinatario DESC LIMIT 5;"
echo ""

# 7. Ver estructura de responsable_emision
echo "7. ESTRUCTURA DE responsable_emision:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "DESCRIBE responsable_emision;"
echo ""

# 8. Ver responsables
echo "8. RESPONSABLES DE EMISIÓN:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM responsable_emision;"
echo ""

# 9. Ver estructura de periodo_emision
echo "9. ESTRUCTURA DE periodo_emision:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "DESCRIBE periodo_emision;"
echo ""

# 10. Ver períodos
echo "10. PERÍODOS DE EMISIÓN:"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM periodo_emision;"
echo ""

# 11. Verificar JOIN entre insigniasotorgadas y destinatario
echo "11. JOIN insigniasotorgadas + destinatario (últimos 5):"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT io.ID_otorgada, io.Codigo_Insignia, io.Destinatario, d.Nombre_Completo, io.Fecha_Emision FROM insigniasotorgadas io LEFT JOIN destinatario d ON io.Destinatario = d.ID_destinatario ORDER BY io.ID_otorgada DESC LIMIT 5;"
echo ""

# 12. Buscar código específico (ejemplo)
echo "12. BUSCAR CÓDIGO ESPECÍFICO (ejemplo TECNM-OFCM-2025-FOR-746):"
mysql -u insignia_user -p'InsigniaTecNM2024!' insignia -e "SELECT * FROM insigniasotorgadas WHERE Codigo_Insignia LIKE 'TECNM-OFCM-2025-FOR-%' ORDER BY ID_otorgada DESC;"
echo ""

echo "=== FIN DE VERIFICACIÓN ==="
