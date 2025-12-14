# Instrucciones para Agregar Campos de Correo a la Tabla it_centros

## Descripción
Este documento contiene las instrucciones para agregar los campos de correo electrónico a la tabla `it_centros` en el servidor.

## Campos a Agregar
- **CE_dir**: Correo electrónico de la dirección del plantel
- **CE_svin**: Correo electrónico de la subdirección de vinculación del plantel
- **CE_saca**: Correo electrónico de la subdirección académica del plantel
- **CE_sadm**: Correo electrónico de la subdirección administrativa del plantel
- **CE_dvin**: Correo electrónico del depto. de vinculación del plantel
- **CE_dcyd**: Correo electrónico del depto. de comunicación y difusión del plantel

## Método 1: Ejecutar desde archivo SQL (Recomendado)

### Paso 1: Subir el archivo al servidor
```bash
# Desde tu máquina local, sube el archivo al servidor
scp agregar_campos_correos_it_centros.sql usuario@servidor:/ruta/destino/
```

### Paso 2: Conectarse al servidor
```bash
ssh usuario@servidor
```

### Paso 3: Ejecutar el script SQL
```bash
mysql -u usuario_mysql -p insignia < agregar_campos_correos_it_centros.sql
```

O si prefieres ejecutarlo desde dentro de MySQL:
```bash
mysql -u usuario_mysql -p
```
Luego dentro de MySQL:
```sql
USE insignia;
source /ruta/completa/agregar_campos_correos_it_centros.sql;
```

## Método 2: Ejecutar comandos directamente en MySQL

### Paso 1: Conectarse a MySQL
```bash
mysql -u usuario_mysql -p
```

### Paso 2: Seleccionar la base de datos
```sql
USE insignia;
```

### Paso 3: Ejecutar los siguientes comandos uno por uno:

```sql
-- Agregar campo: Correo electrónico de la Dirección del plantel
ALTER TABLE it_centros 
ADD COLUMN CE_dir VARCHAR(255) NULL COMMENT 'Correo electrónico de la dirección del plantel';

-- Agregar campo: Correo electrónico de la Subdirección de Vinculación
ALTER TABLE it_centros 
ADD COLUMN CE_svin VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección de vinculación del plantel';

-- Agregar campo: Correo electrónico de la Subdirección Académica
ALTER TABLE it_centros 
ADD COLUMN CE_saca VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección académica del plantel';

-- Agregar campo: Correo electrónico de la Subdirección Administrativa
ALTER TABLE it_centros 
ADD COLUMN CE_sadm VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección administrativa del plantel';

-- Agregar campo: Correo electrónico del Departamento de Vinculación
ALTER TABLE it_centros 
ADD COLUMN CE_dvin VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de vinculación del plantel';

-- Agregar campo: Correo electrónico del Departamento de Comunicación y Difusión
ALTER TABLE it_centros 
ADD COLUMN CE_dcyd VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de comunicación y difusión del plantel';
```

## Verificación

Después de ejecutar los comandos, verifica que los campos se agregaron correctamente:

```sql
DESCRIBE it_centros;
```

O también puedes usar:

```sql
SHOW COLUMNS FROM it_centros;
```

Deberías ver los nuevos campos al final de la lista:
- CE_dir
- CE_svin
- CE_saca
- CE_sadm
- CE_dvin
- CE_dcyd

## Notas Importantes

1. **Backup**: Se recomienda hacer un backup de la base de datos antes de ejecutar estos cambios:
   ```bash
   mysqldump -u usuario_mysql -p insignia > backup_antes_campos_correos_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Permisos**: Asegúrate de tener permisos ALTER en la tabla `it_centros`.

3. **Valores NULL**: Los campos permiten valores NULL, por lo que los registros existentes no se verán afectados.

4. **Tamaño**: Los campos son VARCHAR(255), suficiente para almacenar direcciones de correo electrónico completas.

## Solución de Problemas

### Error: "Duplicate column name"
Si recibes este error, significa que el campo ya existe. Puedes verificar con:
```sql
SHOW COLUMNS FROM it_centros LIKE 'CE_%';
```

### Error: "Access denied"
Verifica que el usuario de MySQL tenga permisos ALTER en la tabla:
```sql
SHOW GRANTS FOR 'usuario_mysql'@'localhost';
```

## Comandos Rápidos (Copia y Pega)

Si prefieres ejecutar todo de una vez, puedes copiar y pegar este bloque completo:

```sql
USE insignia;
ALTER TABLE it_centros ADD COLUMN CE_dir VARCHAR(255) NULL COMMENT 'Correo electrónico de la dirección del plantel';
ALTER TABLE it_centros ADD COLUMN CE_svin VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección de vinculación del plantel';
ALTER TABLE it_centros ADD COLUMN CE_saca VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección académica del plantel';
ALTER TABLE it_centros ADD COLUMN CE_sadm VARCHAR(255) NULL COMMENT 'Correo electrónico de la subdirección administrativa del plantel';
ALTER TABLE it_centros ADD COLUMN CE_dvin VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de vinculación del plantel';
ALTER TABLE it_centros ADD COLUMN CE_dcyd VARCHAR(255) NULL COMMENT 'Correo electrónico del depto. de comunicación y difusión del plantel';
DESCRIBE it_centros;
```

