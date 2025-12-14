# 📋 Plantilla Excel para Insignias Otorgadas

## 📝 Instrucciones

1. Abre Excel o Google Sheets
2. Crea una nueva hoja llamada **"Insignias Otorgadas"** o **"insignias_otorgadas"**
3. Copia los siguientes datos en tu hoja:

## 📊 Datos de Ejemplo (3 filas)

| Codigo_Insignia | Destinatario | Fecha_Emision | Periodo_Emision | Responsable_Emision | Estatus |
|----------------|--------------|--------------|-----------------|---------------------|---------|
| TECNM-OFCM-2025-ART-001 | Juan Pérez Gómez | 2025-01-15 | 1 | 1 | 1 |
| TECNM-OFCM-2025-EMB-002 | María González López | 2025-01-16 | 1 | 1 | 1 |
| TECNM-OFCM-2025-TAL-003 | Carlos Ramírez Martínez | 2025-01-17 | 1 | 1 | 1 |

## 📋 Columnas Requeridas

### ✅ **Obligatorias:**
- **Codigo_Insignia**: Código único de la insignia (ej: TECNM-OFCM-2025-ART-001)
- **Destinatario**: Puede ser:
  - ID numérico del destinatario (ej: 1, 2, 3)
  - Nombre completo (ej: Juan Pérez Gómez)
  - CURP (ej: PEGF850101HDFRZN01)
  - Matrícula (ej: 2025001)
- **Fecha_Emision**: Fecha en formato YYYY-MM-DD (ej: 2025-01-15)

### ⚙️ **Opcionales:**
- **Periodo_Emision**: ID numérico del período (por defecto: NULL)
- **Responsable_Emision**: ID numérico del responsable (por defecto: NULL)
- **Estatus**: ID numérico del estatus (por defecto: 1 = Activo)

## 💡 Notas Importantes

1. **Destinatario**: Si usas nombre completo, CURP o matrícula, el sistema buscará automáticamente el destinatario. Si no existe, lo creará automáticamente.

2. **Código de Insignia**: Debe ser único. El formato recomendado es:
   - `TECNM-OFCM-2025-ART-001` (Embajador del Arte)
   - `TECNM-OFCM-2025-EMB-002` (Embajador del Deporte)
   - `TECNM-OFCM-2025-TAL-003` (Talento Científico)
   - `TECNM-OFCM-2025-INN-004` (Talento Innovador)
   - `TECNM-OFCM-2025-SOC-005` (Responsabilidad Social)
   - `TECNM-OFCM-2025-FOR-006` (Formación y Actualización)
   - `TECNM-OFCM-2025-MOV-007` (Movilidad e Intercambio)

3. **Fecha**: Acepta múltiples formatos:
   - YYYY-MM-DD (2025-01-15)
   - DD/MM/YYYY (15/01/2025)
   - DD-MM-YYYY (15-01-2025)

## 🔍 Ejemplo Completo con Más Detalles

Si quieres usar IDs numéricos en lugar de nombres:

| Codigo_Insignia | Destinatario | Fecha_Emision | Periodo_Emision | Responsable_Emision | Estatus |
|----------------|--------------|--------------|-----------------|---------------------|---------|
| TECNM-OFCM-2025-ART-001 | 1 | 2025-01-15 | 1 | 1 | 1 |
| TECNM-OFCM-2025-EMB-002 | 2 | 2025-01-16 | 1 | 1 | 1 |
| TECNM-OFCM-2025-TAL-003 | 3 | 2025-01-17 | 1 | 1 | 1 |

**Nota**: Los IDs (1, 2, 3) deben existir en la tabla `destinatario`. Si no existen, usa nombres completos y el sistema los creará automáticamente.

## ✅ Pasos para Subir

1. Crea el archivo Excel con la hoja "Insignias Otorgadas"
2. Agrega los headers en la primera fila
3. Agrega tus datos desde la segunda fila
4. Guarda el archivo
5. Ve a la carga masiva y sube el archivo
6. Verifica que los datos se hayan cargado correctamente
7. Prueba la consulta pública para verificar que aparezcan

