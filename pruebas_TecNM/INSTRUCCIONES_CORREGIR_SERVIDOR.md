# 🔧 Instrucciones para Corregir el Servidor

## 📋 Resumen de Problemas Encontrados

1. ❌ **FALTA `verificar_sesion.php`** en el servidor
2. ❌ **Error fatal**: `Cannot redeclare detectarEntorno()` - función declarada dos veces
3. ❌ **Funciones no disponibles** porque falta `verificar_sesion.php`

---

## ✅ Solución: Pasos a Ejecutar en el Servidor

### Paso 1: Subir `verificar_sesion.php` al servidor

En PuTTY, ejecuta:

```bash
cd /var/www/html
sudo wget https://raw.githubusercontent.com/VictorJonathanCastro/Insignias_TecNM_Funcional/main/verificar_sesion.php -O verificar_sesion.php
sudo chown www-data:www-data verificar_sesion.php
sudo chmod 644 verificar_sesion.php
```

### Paso 2: Corregir `conexion.php` en el servidor

**Opción A: Editar manualmente (RECOMENDADO)**

```bash
cd /var/www/html
sudo nano conexion.php
```

Busca esta línea (alrededor de la línea 8):
```php
function detectarEntorno() {
```

Cámbiala por:
```php
if (!function_exists('detectarEntorno')) {
    function detectarEntorno() {
```

Y al final de la función (después de `return 'desconocido';`), agrega:
```php
    }
}
```

**Opción B: Usar comando sed (más rápido)**

```bash
cd /var/www/html
sudo cp conexion.php conexion.php.backup
sudo sed -i '8s/^function detectarEntorno() {/if (!function_exists('\''detectarEntorno'\'')) {\n    function detectarEntorno() {/' conexion.php
sudo sed -i '/^    return '\''desconocido'\'';$/a\    }\n}' conexion.php
```

### Paso 3: Verificar permisos

```bash
sudo chown www-data:www-data conexion.php verificar_sesion.php
sudo chmod 644 conexion.php verificar_sesion.php
```

### Paso 4: Verificar que funcionó

Abre en tu navegador:
```
http://158.23.160.163/diagnostico_servidor.php
```

Deberías ver:
- ✅ `verificar_sesion.php`: Existe
- ✅ Todas las funciones disponibles
- ✅ Sin errores fatales

---

## 🎯 Después de Corregir

1. **Limpia las cookies** del navegador (Ctrl+Shift+Delete)
2. **Intenta iniciar sesión** de nuevo
3. **Debería funcionar** correctamente

---

## ⚠️ Si algo sale mal

Si el archivo `conexion.php` se daña, restaura el backup:

```bash
cd /var/www/html
sudo cp conexion.php.backup conexion.php
```

