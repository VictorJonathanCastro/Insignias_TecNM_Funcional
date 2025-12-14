# ✅ Lista de Pruebas para Verificar el Sistema

## 🗄️ Paso 1: Verificar Base de Datos

### Comando 1: Verificar categorías en la BD
```bash
mysql -u insignia_user -pInsigniaTecNM2024! insignia -e "SELECT * FROM cat_insignias ORDER BY id;"
```

**Resultado esperado:**
- Debe mostrar solo 3 categorías:
  - id=1: Formacion Integral
  - id=2: Docencia
  - id=3: Academia

---

### Comando 2: Verificar que existe la columna Cat_ins
```bash
mysql -u insignia_user -pInsigniaTecNM2024! insignia -e "DESCRIBE tipo_insignia;"
```

**Resultado esperado:**
- Debe mostrar la columna `Cat_ins` en la lista

---

### Comando 3: Verificar relaciones (tipos de insignia con categorías)
```bash
mysql -u insignia_user -pInsigniaTecNM2024! insignia -e "SELECT ti.id, ti.Nombre_Insignia, ti.Cat_ins, ci.Nombre_cat as Categoria FROM tipo_insignia ti LEFT JOIN cat_insignias ci ON ti.Cat_ins = ci.id ORDER BY ci.Nombre_cat, ti.Nombre_Insignia;"
```

**Resultado esperado:**
- Formacion Integral debe tener: Embajador del Deporte, Embajador del Arte, Responsabilidad Social, Movilidad e Intercambio, Innovacion
- Docencia debe tener: Formacion y Actualizacion
- Academia debe tener: Talento Cientifico

---

## 🌐 Paso 2: Verificar Formulario de Metadatos

### Prueba 1: Acceder al formulario
1. Abre en el navegador: `https://tudominio.com/metadatos_formulario.php`
2. Debes poder acceder sin errores

---

### Prueba 2: Verificar categorías en el select
1. En el formulario, busca el campo "Categoría"
2. Debe mostrar **exactamente 3 opciones**:
   - Formacion Integral
   - Docencia
   - Academia

**❌ Si ves más de 3 categorías o diferentes nombres, hay un problema**

---

### Prueba 3: Seleccionar categoría y ver subcategorías

#### Categoría: Formacion Integral
1. Selecciona "Formacion Integral"
2. En "Subcategoría" debe aparecer:
   - Embajador del Deporte
   - Embajador del Arte
   - Responsabilidad Social
   - Movilidad e Intercambio
   - Talento Innovador (o Innovacion)

#### Categoría: Docencia
1. Selecciona "Docencia"
2. En "Subcategoría" debe aparecer:
   - Formación y Actualización

#### Categoría: Academia
1. Selecciona "Academia"
2. En "Subcategoría" debe aparecer:
   - Talento Científico

---

### Prueba 4: Probar registro completo
1. Selecciona una categoría
2. Selecciona una subcategoría
3. Llena los demás campos del formulario
4. Envía el formulario
5. Debe guardar correctamente sin errores

---

## 🔍 Paso 3: Verificar Otros Módulos

### Prueba: Consulta Pública
1. Accede a: `https://tudominio.com/consulta_publica.php`
2. Debe cargar sin errores
3. Verifica que los filtros de categoría funcionen

---

### Prueba: Historial de Insignias
1. Accede a: `https://tudominio.com/historial_insignias.php`
2. Debe mostrar las insignias correctamente
3. Verifica que las categorías se muestren bien

---

## 📋 Checklist de Verificación

- [ ] Base de datos tiene solo 3 categorías
- [ ] Columna `Cat_ins` existe en `tipo_insignia`
- [ ] Relaciones están asignadas correctamente
- [ ] Formulario de metadatos muestra 3 categorías
- [ ] Al seleccionar "Formacion Integral" aparecen 5 subcategorías
- [ ] Al seleccionar "Docencia" aparece 1 subcategoría
- [ ] Al seleccionar "Academia" aparece 1 subcategoría
- [ ] Se puede registrar una insignia sin errores
- [ ] Otros módulos funcionan correctamente

---

## 🆘 Si Algo No Funciona

### Error: "No aparecen las categorías correctas"
- Verifica la base de datos con el Comando 1
- Verifica que los archivos PHP se actualizaron con `git pull`

### Error: "No aparecen subcategorías al seleccionar categoría"
- Verifica las relaciones en la BD con el Comando 3
- Verifica que la columna `Cat_ins` existe

### Error: "Error al guardar"
- Revisa los logs del servidor
- Verifica que la conexión a la BD funciona
- Verifica que los campos requeridos están llenos

---

## ✅ Todo Listo Cuando...

1. ✅ El formulario muestra 3 categorías
2. ✅ Al seleccionar cada categoría aparecen las subcategorías correctas
3. ✅ Se puede registrar una insignia exitosamente
4. ✅ La base de datos tiene la estructura correcta

