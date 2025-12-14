# 🎓 Sistema de Insignias Digitales TecNM

## 📋 Descripción del Proyecto

El Sistema de Insignias Digitales TecNM es una plataforma web desarrollada para modernizar el proceso de otorgamiento, gestión y verificación de insignias académicas y profesionales dentro del Tecnológico Nacional de México.

### ✨ Características Principales

- **Gestión Completa de Insignias**: Creación, edición y administración de insignias digitales
- **Metadatos Completos**: Sistema robusto de metadatos para validación y verificación
- **Carga Masiva via Excel**: Importación masiva de datos desde archivos Excel
- **Verificación Pública**: Sistema de verificación abierto para validar insignias
- **Integración Social**: Compartir insignias en redes sociales
- **Panel Administrativo**: Interfaz completa para administradores
- **API REST**: Endpoints para integraciones futuras

## 🚀 Instalación Rápida

### Requisitos del Sistema

- **PHP**: 7.4 o superior
- **MySQL**: 8.0 o superior
- **Apache/Nginx**: Servidor web
- **Composer**: Gestor de dependencias PHP
- **Extensiones PHP**: mysqli, gd, curl, zip

### Instalación Automática

```bash
# 1. Clonar o descargar el proyecto
cd /ruta/del/proyecto

# 2. Ejecutar script de instalación
chmod +x instalar.sh
./instalar.sh

# 3. Configurar base de datos
# Editar conexion.php con sus credenciales

# 4. Importar estructura de base de datos
mysql -u root -p Insignia_Funcional < BD/estructura_completa_con_metadatos.sql
```

### Instalación Manual

```bash
# 1. Instalar dependencias
composer install

# 2. Crear directorios necesarios
mkdir uploads logs
chmod 755 uploads logs

# 3. Configurar permisos
chmod 755 .
chmod 644 *.php
```

## 📊 Funcionalidad de Carga Masiva

### Tipos de Carga Disponibles

1. **Insignias Otorgadas** (`T_insignias_otorgadas`)
2. **Destinatarios** (`destinatario`)
3. **Centros IT** (`it_centros`)
4. **Tipos de Insignia** (`tipo_insignia`)
5. **Categorías de Insignia** (`cat_insignias`)
6. **Periodos de Emisión** (`periodo_emision`)

### Uso del Sistema de Carga Masiva

1. **Acceder al módulo**: `carga_masiva_excel.php`
2. **Descargar plantilla**: Seleccionar tipo y descargar plantilla Excel
3. **Completar datos**: Llenar la plantilla con los datos requeridos
4. **Cargar archivo**: Subir el archivo Excel completado
5. **Revisar resultados**: Verificar éxitos y errores del proceso

## 🗄️ Estructura de Base de Datos

### Tablas Principales

- **`tipo_insignia`**: Tipos de insignias disponibles
- **`it_centros`**: Centros tecnológicos del TecNM
- **`cat_insignias`**: Categorías de insignias
- **`estatus`**: Estados de las insignias
- **`periodo_emision`**: Periodos escolares
- **`destinatario`**: Estudiantes que reciben insignias
- **`responsable_emision`**: Responsables de otorgar insignias
- **`T_insignias`**: Insignias maestras definidas
- **`T_insignias_otorgadas`**: Insignias ya entregadas
- **`Usuario`**: Usuarios del sistema

### Vista de Metadatos Completos

La vista `T_metadatos_completos` proporciona acceso a todos los metadatos requeridos:

1. Código de identificación de la InsigniaTecNM
2. Nombre de la InsigniaTecNM (Subcategoría)
3. Categoría de la InsigniaTecNM
4. Destinatario
5. Descripción
6. Criterios para su emisión
7. Fecha de emisión
8. Emisor (TecNM o Instituto/Centro)
9. Evidencia
10. Archivo Visual de la InsigniaTecNM
11. Responsable de la captura de los Metadatos
12. Código de identificación del Responsable

## 🔧 Configuración

### Archivo de Conexión (`conexion.php`)

```php
// Configuración para XAMPP
$servidor = "127.0.0.1";
$usuario = "root";
$password = "";
$bd = "Insignia_Funcional";
$puerto = 3306;

// Configuración para Ubuntu/Linux
$servidor = "localhost";
$usuario = "root";
$password = "tu_password";
$bd = "Insignia_Funcional";
$puerto = 3306;
```

### Variables de Entorno

Para producción, considere usar variables de entorno:

```bash
# .env
DB_HOST=localhost
DB_USER=usuario_bd
DB_PASS=password_seguro
DB_NAME=Insignia_Funcional
```

## 📱 Funcionalidades del Sistema

### Para Administradores

- **Gestión de Usuarios**: Crear y administrar cuentas
- **Gestión de Insignias**: Crear y editar insignias
- **Carga Masiva**: Importar datos desde Excel
- **Reportes**: Generar reportes de insignias otorgadas
- **Configuración**: Ajustar parámetros del sistema

### Para Estudiantes

- **Dashboard Personal**: Ver insignias recibidas
- **Verificación**: Validar insignias propias
- **Compartir**: Compartir insignias en redes sociales
- **Descargar**: Descargar certificados digitales

### Para Público General

- **Verificación Pública**: Validar cualquier insignia
- **Búsqueda**: Buscar insignias por código
- **Consulta**: Ver información pública de insignias

## 🔒 Seguridad

### Medidas Implementadas

- **Validación de Entrada**: Sanitización de todos los datos
- **Prepared Statements**: Prevención de SQL Injection
- **Sesiones Seguras**: Manejo seguro de sesiones
- **Validación de Archivos**: Verificación de tipos y tamaños
- **Logs de Auditoría**: Registro de actividades importantes

### Recomendaciones

- Cambiar contraseñas por defecto
- Usar HTTPS en producción
- Configurar firewall apropiadamente
- Realizar backups regulares
- Mantener el sistema actualizado

## 📈 Escalabilidad

### Consideraciones para Producción

- **Máquina Virtual**: Solicitar MV con características similares a AlfabetizaTec
- **Base de Datos**: Considerar replicación para alta disponibilidad
- **CDN**: Para servir imágenes de insignias
- **Cache**: Implementar cache para consultas frecuentes
- **Monitoreo**: Sistema de monitoreo y alertas

### Especificaciones Recomendadas para MV

- **Sistema Operativo**: Ubuntu Server 20.04 LTS
- **RAM**: 16 GB (mínimo 8 GB)
- **Almacenamiento**: 100 GB SSD
- **CPU**: 4 vCPU
- **Red**: IP pública con acceso estable
- **Software**: Apache/Nginx, MySQL 8.0, PHP 8.1+

## 🐛 Solución de Problemas

### Errores Comunes

1. **Error de Conexión a BD**
   - Verificar que MySQL esté corriendo
   - Revisar credenciales en `conexion.php`
   - Confirmar que la BD existe

2. **Error al Subir Archivos Excel**
   - Verificar permisos del directorio `uploads/`
   - Confirmar que PhpSpreadsheet está instalado
   - Revisar límites de PHP (upload_max_filesize)

3. **Problemas de Permisos**
   - Ejecutar: `chmod 755 uploads/ logs/`
   - Verificar propietario de archivos

### Logs del Sistema

Los logs se almacenan en el directorio `logs/`:
- `error.log`: Errores del sistema
- `access.log`: Accesos y actividades
- `upload.log`: Actividades de carga masiva


## 📄 Licencia

Este proyecto está desarrollado para el Tecnológico Nacional de México y está sujeto a las políticas institucionales correspondientes.



