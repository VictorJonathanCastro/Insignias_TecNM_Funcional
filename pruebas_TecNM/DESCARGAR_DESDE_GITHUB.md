# 📥 Cómo Descargar los Archivos desde GitHub al Servidor

## Opción 1: Usando Git en el Servidor (Más Fácil) ⭐

Si tu servidor tiene Git instalado, simplemente ejecuta:

```bash
# Navega a la carpeta del proyecto en el servidor
cd /ruta/a/tu/proyecto

# Descarga los últimos cambios
git pull origin main
```

¡Listo! Los archivos se actualizarán automáticamente.

---

## Opción 2: Descargar Manualmente desde GitHub

### Paso 1: Ir a GitHub
1. Ve a: `https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional`
2. Asegúrate de estar en la rama `main`

### Paso 2: Descargar Archivos Específicos

**Método A: Descargar archivos individuales**
1. Haz clic en el archivo que necesitas (ej: `metadatos_formulario.php`)
2. Haz clic en el botón "Raw" (código crudo)
3. Copia todo el contenido
4. Pégalo en el archivo correspondiente en tu servidor

**Método B: Descargar todo el repositorio**
1. Haz clic en el botón verde "Code"
2. Selecciona "Download ZIP"
3. Extrae el ZIP
4. Sube los archivos modificados al servidor:
   - `metadatos_formulario.php`
   - `agregar_relacion_categorias.php`
   - `BD/backup_sistema_funcional.sql`

---

## Opción 3: Usando cPanel File Manager

1. Inicia sesión en cPanel
2. Ve a "File Manager"
3. Navega a la carpeta de tu proyecto
4. Para cada archivo modificado:
   - Haz clic derecho → "Edit"
   - Copia el contenido desde GitHub (botón "Raw")
   - Pega y guarda

---

## Archivos que Necesitas Actualizar

✅ **metadatos_formulario.php** - Formulario principal
✅ **agregar_relacion_categorias.php** - Script de actualización
✅ **BD/backup_sistema_funcional.sql** - Backup actualizado

---

## ⚠️ IMPORTANTE: Después de Descargar

**NO OLVIDES actualizar la base de datos** ejecutando el script SQL en phpMyAdmin o usando `agregar_relacion_categorias.php`

