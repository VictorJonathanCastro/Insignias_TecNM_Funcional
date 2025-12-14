# 📋 Guía para Cargar tu Archivo bds.xlsx

## ✅ Lo que SÍ funcionará sin errores:

### 1. **Estructura del Excel**
Tu archivo `bds.xlsx` debe tener:
- **Una hoja por cada tabla** que quieras cargar
- **Primera fila con headers** (nombres de columnas)
- **Datos desde la segunda fila** en adelante

### 2. **Nombres de Hojas Recomendados**
El sistema detecta automáticamente el tipo de tabla por el **nombre de la hoja** o por los **headers**. Usa estos nombres:

| Nombre de Hoja | Tabla que se carga |
|----------------|-------------------|
| `Destinatarios` o `destinatarios` | destinatario |
| `Centros IT` o `centros_it` | it_centros |
| `Tipos de Insignia` o `tipos_insignia` | tipo_insignia |
| `Categorías` o `categorias_insignia` | cat_insignias |
| `Periodos` o `periodos_emision` | periodo_emision |
| `Estatus` o `estatus` | estatus |
| `Responsables` o `responsables_emision` | responsable_emision |
| `Insignias Maestras` o `insignias_maestras` | T_insignias |
| `Usuarios` o `usuarios` | Usuario |
| `Insignias Otorgadas` o `insignias_otorgadas` | T_insignias_otorgadas o insigniasotorgadas |

### 3. **Headers Requeridos (Columnas)**

#### **Destinatarios:**
- `Id_Centro` (requerido)
- `Nombre_Completo` (requerido)
- `Nombre` (requerido)
- `Apellido_Paterno` (requerido)
- `Apellido_Materno` (requerido)
- `Curp` (opcional)
- `Matricula` (opcional)
- `Correo` (opcional)
- `Telefono` (opcional)
- `Rol` (opcional)

#### **Centros IT:**
- `Nombre_itc` (requerido)
- `Acron` (opcional)
- `Estado` (opcional)
- `Clave_ct` (opcional)
- `Tipo_itc` (opcional)

#### **Tipos de Insignia:**
- `Nombre_Insignia` (requerido)
- `Descripcion` (opcional)
- `Id_Categoria` (opcional)

#### **Categorías de Insignia:**
- `Nombre_Cat` (requerido)
- `Descripcion` (opcional)

#### **Periodos de Emisión:**
- `Periodo` (requerido)
- `Anio` (opcional)
- `Fecha_Inicio` (opcional)
- `Fecha_Fin` (opcional)

#### **Estatus:**
- `Nombre_Estatus` (requerido)
- `Acron_Estatus` (opcional)

#### **Responsables de Emisión:**
- `Nombre_Completo` (requerido)
- `Adscripcion` (requerido - debe ser ID numérico de it_centros)
- `Cargo` (opcional)
- `Codigo_Identificacion` (opcional)
- `Correo` (opcional)
- `Telefono` (opcional)

#### **Insignias Maestras (T_insignias):**
- `Tipo_Insignia` (requerido - ID numérico)
- `Propone_Insignia` (requerido - ID numérico de it_centros)
- `Programa` (opcional)
- `Descripcion` (requerido)
- `Criterio` (requerido)
- `Fecha_Creacion` (opcional)
- `Fecha_Autorizacion` (opcional)
- `Nombre_gen_ins` (opcional)
- `Estatus` (requerido - ID numérico)
- `Archivo_Visual` (opcional)

#### **Usuarios:**
- `Nombre` (requerido)
- `Apellido_Paterno` (requerido)
- `Apellido_Materno` (opcional)
- `Correo` (requerido - debe ser email válido)
- `Contrasena` (requerido - se hasheará automáticamente)
- `Rol` (opcional - Admin, SuperUsuario, Estudiante)
- `Estado` (opcional - Activo, Inactivo)
- `It_Centro_Id` (opcional - ID numérico)

#### **Insignias Otorgadas:**
- `Id_Insignia` (requerido - ID numérico)
- `Id_Destinatario` (requerido - ID numérico)
- `Fecha_Emision` (requerido - formato fecha)
- `Id_Periodo_Emision` (requerido - ID numérico)
- `Id_Estatus` (requerido - ID numérico)

---

## ⚠️ Posibles Errores y Cómo Evitarlos:

### Error 1: "No se pudo detectar el tipo de tabla"
**Causa:** El nombre de la hoja o los headers no coinciden con los esperados.

**Solución:**
- Usa los nombres de hoja recomendados arriba
- O asegúrate de que los headers coincidan exactamente (pueden tener mayúsculas/minúsculas diferentes)

### Error 2: "Campo X es requerido"
**Causa:** Faltan datos en alguna fila.

**Solución:**
- Completa todos los campos requeridos
- O deja la fila vacía si no quieres cargarla

### Error 3: "ID debe ser numérico"
**Causa:** Estás poniendo texto donde debe ir un número (ej: en `Id_Centro`, `Adscripcion`, etc.)

**Solución:**
- Asegúrate de que los IDs sean números (1, 2, 3, etc.)
- Si no conoces el ID, primero carga la tabla relacionada (ej: carga Centros IT primero para obtener sus IDs)

### Error 4: "Correo inválido"
**Causa:** El formato del correo no es válido.

**Solución:**
- Usa formato correcto: `usuario@dominio.com`

### Error 5: "Foreign key constraint fails"
**Causa:** Estás referenciando un ID que no existe en otra tabla.

**Solución:**
- **Orden recomendado de carga:**
  1. Primero: `Centros IT` (para obtener IDs)
  2. Segundo: `Categorías de Insignia`
  3. Tercero: `Tipos de Insignia` (necesita categorías)
  4. Cuarto: `Estatus`
  5. Quinto: `Periodos de Emisión`
  6. Sexto: `Responsables de Emisión` (necesita Centros IT)
  7. Séptimo: `Destinatarios` (necesita Centros IT)
  8. Octavo: `Usuarios` (opcional, necesita Centros IT)
  9. Noveno: `Insignias Maestras` (necesita Tipos, Centros IT, Estatus)
  10. Último: `Insignias Otorgadas` (necesita Insignias Maestras, Destinatarios, Periodos, Estatus)

---

## 🎯 Recomendación para tu archivo bds.xlsx:

### Opción A: Cargar TODO en un solo archivo (Recomendado)
1. Crea un Excel con múltiples hojas
2. Cada hoja = una tabla
3. Nombra las hojas con los nombres recomendados
4. Selecciona "TODAS LAS TABLAS (Carga Completa)"
5. Si quieres firmar, marca la casilla (solo afectará Insignias Otorgadas)
6. Sube el archivo

### Opción B: Cargar tabla por tabla
1. Descarga las plantillas primero
2. Llena cada plantilla
3. Carga una tabla a la vez en el orden recomendado

---

## ✅ Verificación Antes de Cargar:

1. ✅ ¿Todas las hojas tienen headers en la primera fila?
2. ✅ ¿Los nombres de las hojas son reconocibles?
3. ✅ ¿Los IDs referenciados existen en otras tablas?
4. ✅ ¿Los campos requeridos están completos?
5. ✅ ¿Los formatos de fecha son correctos (YYYY-MM-DD)?

---

## 🔍 Si hay errores:

El sistema te mostrará:
- ✅ **Éxitos:** Hojas que se cargaron correctamente
- ❌ **Errores:** Hojas con problemas y el motivo

**Ejemplo de salida:**
```
✅ Hoja 'Destinatarios' procesada correctamente
✅ Hoja 'Centros IT' procesada correctamente
❌ Hoja 'Insignias Otorgadas': Fila 5 - Id_Destinatario debe ser numérico
```

Puedes corregir el Excel y volver a cargar solo las hojas que fallaron.

---

## 💡 Tips Finales:

1. **Descarga las plantillas primero** para ver los headers exactos
2. **Carga en el orden recomendado** para evitar errores de foreign keys
3. **Si una hoja falla, las demás siguen procesándose** (no se detiene todo)
4. **La firma digital es opcional** - solo marca la casilla si quieres firmar Insignias Otorgadas
5. **Revisa los mensajes de éxito/error** después de cargar para ver qué se procesó

