# 🚀 Comandos Exactos para Actualizar en el Servidor

## Ruta Confirmada: `/var/www/html/`

## Pasos para Actualizar desde GitHub:

### 1. Ir a la carpeta del proyecto:
```bash
cd /var/www/html
```

### 2. Verificar el estado actual:
```bash
git status
```

### 3. Descargar los últimos cambios desde GitHub:
```bash
git pull origin main
```

### 4. Verificar que se actualizaron los archivos:
```bash
git log --oneline -1
```

Deberías ver: "Actualizar categorías: Formación Integral, Docencia, Academia..."

---

## ✅ Comandos Completos (Copia y Pega):

```bash
cd /var/www/html
git status
git pull origin main
git log --oneline -1
```

---

## 🗄️ IMPORTANTE: Después de Actualizar los Archivos

**NO OLVIDES actualizar la base de datos** en phpMyAdmin con este SQL:

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

## 🎯 Verificar que Funcionó

Después de actualizar, accede a:
- `https://tudominio.com/metadatos_formulario.php`

Deberías ver las 3 categorías: **Formación Integral**, **Docencia**, **Academia**

