# Instrucciones para Subir Cambios al Servidor

## 📋 Archivos Modificados

Los siguientes archivos fueron modificados y necesitan subirse al servidor:

1. **metadatos_formulario.php** - Formulario de metadatos con categorías corregidas
2. **agregar_relacion_categorias.php** - Asignaciones de categorías actualizadas
3. **BD/backup_sistema_funcional.sql** - Backup con las 3 categorías correctas

---

## 🔄 Paso 1: Subir Archivos PHP al Servidor

### Opción A: Usando FTP/SFTP (FileZilla, WinSCP, etc.)

1. Conecta a tu servidor usando tus credenciales FTP/SFTP
2. Navega a la carpeta del proyecto en el servidor
3. Sube estos archivos (sobrescribiendo los existentes):
   - `metadatos_formulario.php`
   - `agregar_relacion_categorias.php`

### Opción B: Usando Git (si tienes repositorio)

```bash
git add metadatos_formulario.php agregar_relacion_categorias.php
git commit -m "Actualizar categorías: Formación Integral, Docencia, Academia"
git push origin main
```

### Opción C: Usando cPanel File Manager

1. Inicia sesión en cPanel
2. Ve a "File Manager"
3. Navega a la carpeta del proyecto
4. Sube los archivos modificados

---

## 🗄️ Paso 2: Actualizar la Base de Datos en el Servidor

### IMPORTANTE: Haz un backup de la base de datos antes de hacer cambios

### Opción A: Usando phpMyAdmin

1. Inicia sesión en phpMyAdmin
2. Selecciona tu base de datos (probablemente `insignia`)
3. Ve a la pestaña "SQL"
4. Ejecuta estos comandos SQL:

```sql
-- Primero, eliminar las categorías antiguas (si existen)
DELETE FROM cat_insignias WHERE id IN (4, 5);

-- Actualizar las categorías existentes
UPDATE cat_insignias SET Nombre_cat = 'Formacion Integral', Acron_cat = 'FI' WHERE id = 1;
UPDATE cat_insignias SET Nombre_cat = 'Docencia', Acron_cat = 'DOC' WHERE id = 2;
UPDATE cat_insignias SET Nombre_cat = 'Academia', Acron_cat = 'ACA' WHERE id = 3;

-- Si no existen, insertarlas
INSERT IGNORE INTO cat_insignias (id, Nombre_cat, Acron_cat, Fecha_Creacion) VALUES
(1, 'Formacion Integral', 'FI', '2024-10-01'),
(2, 'Docencia', 'DOC', '2024-10-01'),
(3, 'Academia', 'ACA', '2024-10-01');
```

### Opción B: Usando el script agregar_relacion_categorias.php

1. Sube el archivo `agregar_relacion_categorias.php` al servidor
2. Accede a: `https://tudominio.com/agregar_relacion_categorias.php`
3. El script verificará y actualizará las relaciones automáticamente
4. **IMPORTANTE**: Elimina este archivo del servidor después de usarlo por seguridad

### Opción C: Actualizar relaciones manualmente

Si la tabla `tipo_insignia` tiene la columna `Cat_ins`, ejecuta:

```sql
-- Asignar tipos de insignia a Formación Integral (id=1)
UPDATE tipo_insignia SET Cat_ins = 1 WHERE id IN (7, 8, 1, 6, 3);
-- 7 = Embajador del Deporte
-- 8 = Embajador del Arte
-- 1 = Responsabilidad Social
-- 6 = Movilidad e Intercambio
-- 3 = Innovacion (Talento Innovador)

-- Asignar tipos de insignia a Docencia (id=2)
UPDATE tipo_insignia SET Cat_ins = 2 WHERE id = 9;
-- 9 = Formacion y Actualizacion

-- Asignar tipos de insignia a Academia (id=3)
UPDATE tipo_insignia SET Cat_ins = 3 WHERE id = 10;
-- 10 = Talento Cientifico
```

---

## ✅ Paso 3: Verificar que Todo Funcione

1. **Verificar categorías en la base de datos:**
   ```sql
   SELECT * FROM cat_insignias ORDER BY id;
   ```
   Deberías ver solo 3 categorías: Formacion Integral, Docencia, Academia

2. **Verificar relaciones (si existe Cat_ins):**
   ```sql
   SELECT ti.id, ti.Nombre_Insignia, ti.Cat_ins, ci.Nombre_cat 
   FROM tipo_insignia ti 
   LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id 
   ORDER BY ci.Nombre_cat, ti.Nombre_Insignia;
   ```

3. **Probar el formulario de metadatos:**
   - Accede a `metadatos_formulario.php`
   - Verifica que aparezcan las 3 categorías correctas
   - Selecciona una categoría y verifica que aparezcan las subcategorías correctas

---

## 🔒 Paso 4: Seguridad (IMPORTANTE)

Después de actualizar, asegúrate de:

1. **Eliminar archivos temporales** del servidor:
   - `agregar_relacion_categorias.php` (si lo usaste)
   - Cualquier archivo de prueba o debug

2. **Verificar permisos** de archivos:
   - Archivos PHP: 644
   - Carpetas: 755

3. **Verificar conexión a BD** en `conexion.php`:
   - Asegúrate de que tenga las credenciales correctas del servidor

---

## 📝 Resumen de Estructura Final

**Categoría: Formación Integral (id=1)**
- Embajador del Deporte
- Embajador del Arte
- Responsabilidad Social
- Movilidad e Intercambio
- Talento Innovador (Innovacion)

**Categoría: Docencia (id=2)**
- Formación y Actualización

**Categoría: Academia (id=3)**
- Talento Científico

---

## ⚠️ Notas Importantes

- **SIEMPRE haz backup** de la base de datos antes de hacer cambios
- Si tienes datos importantes en producción, prueba primero en un entorno de desarrollo
- Verifica que no haya errores después de subir los cambios
- Si algo no funciona, puedes restaurar desde el backup

---

## 🆘 Si Algo Sale Mal

1. Restaura el backup de la base de datos
2. Restaura los archivos PHP desde tu backup local
3. Verifica los logs de error del servidor
4. Revisa la configuración de conexión a la base de datos

