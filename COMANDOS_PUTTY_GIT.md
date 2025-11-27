# 🖥️ Comandos para Actualizar desde GitHub usando PuTTY

## Pasos para Actualizar en el Servidor

### 1. Conectarte al Servidor con PuTTY
- Abre PuTTY
- Ingresa la IP o dominio de tu servidor
- Conéctate con tus credenciales SSH

### 2. Navegar a la Carpeta del Proyecto
```bash
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional
```

**Nota:** La ruta típica puede ser:
- `/var/www/html/Insignias_TecNM_Funcional`
- `/home/usuario/public_html/Insignias_TecNM_Funcional`
- `/var/www/Insignias_TecNM_Funcional`

### 3. Verificar el Estado Actual
```bash
git status
```

### 4. Descargar los Últimos Cambios desde GitHub
```bash
git pull origin main
```

### 5. Verificar que se Actualizaron los Archivos
```bash
git log --oneline -5
```

Deberías ver el commit: "Actualizar categorías: Formación Integral, Docencia, Academia..."

---

## ⚠️ Si hay Conflictos o Cambios Locales

Si Git te dice que hay cambios locales que no se han guardado:

### Opción A: Guardar cambios locales primero
```bash
git stash
git pull origin main
git stash pop
```

### Opción B: Descartar cambios locales (CUIDADO: perderás cambios locales)
```bash
git reset --hard
git pull origin main
```

---

## ✅ Verificar Archivos Actualizados

Después del pull, verifica que los archivos se actualizaron:

```bash
ls -la metadatos_formulario.php agregar_relacion_categorias.php
```

O ver el contenido de un archivo:

```bash
head -20 metadatos_formulario.php
```

---

## 📋 Resumen de Comandos (Copia y Pega)

```bash
# 1. Ir a la carpeta del proyecto
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional

# 2. Verificar estado
git status

# 3. Descargar cambios
git pull origin main

# 4. Verificar que funcionó
git log --oneline -1
```

---

## 🗄️ IMPORTANTE: Después de Actualizar los Archivos

**NO OLVIDES actualizar la base de datos** ejecutando el script SQL en phpMyAdmin:

```sql
-- Actualizar categorías
UPDATE cat_insignias SET Nombre_cat = 'Formacion Integral', Acron_cat = 'FI' WHERE id = 1;
UPDATE cat_insignias SET Nombre_cat = 'Docencia', Acron_cat = 'DOC' WHERE id = 2;
UPDATE cat_insignias SET Nombre_cat = 'Academia', Acron_cat = 'ACA' WHERE id = 3;

-- Eliminar categorías antiguas
DELETE FROM cat_insignias WHERE id IN (4, 5);

-- Actualizar relaciones (si existe Cat_ins)
UPDATE tipo_insignia SET Cat_ins = 1 WHERE id IN (7, 8, 1, 6, 3);
UPDATE tipo_insignia SET Cat_ins = 2 WHERE id = 9;
UPDATE tipo_insignia SET Cat_ins = 3 WHERE id = 10;
```

---

## 🆘 Si Algo Sale Mal

Si el pull falla, intenta:

```bash
# Ver qué está pasando
git status

# Ver los últimos commits
git log --oneline -10

# Forzar actualización (CUIDADO)
git fetch origin
git reset --hard origin/main
```

