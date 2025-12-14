# 📦 Instalación de Composer en Windows para Sistema de Insignias TecNM

## 🚀 Instalación Rápida

### Opción 1: Instalador Automático (Recomendado)

1. **Descargar Composer**
   - Visitar: https://getcomposer.org/download/
   - Hacer clic en "Composer-Setup.exe"
   - Descargar e ejecutar el instalador

2. **Ejecutar Instalador**
   - El instalador detectará automáticamente PHP
   - Seguir las instrucciones en pantalla
   - Composer se instalará globalmente

3. **Verificar Instalación**
   ```cmd
   composer --version
   ```

### Opción 2: Instalación Manual

1. **Descargar Composer**
   ```cmd
   cd C:\xampp\htdocs\Insignias_TecNM_Funcional
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php -r "unlink('composer-setup.php');"
   ```

2. **Mover a Directorio Global**
   ```cmd
   move composer.phar C:\Windows\composer.phar
   ```

3. **Crear Archivo Batch**
   - Crear archivo `composer.bat` en `C:\Windows\`
   - Contenido:
   ```batch
   @echo off
   php "C:\Windows\composer.phar" %*
   ```

## 🔧 Configuración del Proyecto

### 1. Instalar Dependencias

```cmd
cd C:\xampp\htdocs\Insignias_TecNM_Funcional
composer install
```

### 2. Verificar Instalación

```cmd
composer show phpoffice/phpspreadsheet
```

### 3. Probar Sistema

- Abrir navegador
- Ir a: `http://localhost/Insignias_TecNM_Funcional/carga_masiva_excel.php`

## 🐛 Solución de Problemas

### Error: "composer no se reconoce como comando"

**Solución:**
1. Agregar PHP al PATH del sistema
2. Reiniciar terminal
3. Verificar con: `php --version`

### Error: "Extension php_zip not found"

**Solución:**
1. Abrir `php.ini` en XAMPP
2. Descomentar línea: `extension=zip`
3. Reiniciar Apache

### Error: "Memory limit exceeded"

**Solución:**
1. Editar `php.ini`
2. Cambiar: `memory_limit = 512M`
3. Reiniciar Apache

## 📋 Comandos Útiles

```cmd
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Ver dependencias instaladas
composer show

# Verificar autoloader
composer dump-autoload

# Limpiar cache
composer clear-cache
```

## ✅ Verificación Final

Después de la instalación, verificar que todo funciona:

1. **Composer instalado**: `composer --version`
2. **PHP funcionando**: `php --version`
3. **Dependencias instaladas**: `composer show`
4. **Sistema accesible**: Abrir en navegador

---

**¡Excelente tarde equipo! 🎓**

*Sistema desarrollado con ❤️ para el TecNM*
