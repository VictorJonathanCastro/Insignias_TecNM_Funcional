# Instrucciones para Corregir Duplicado "Aprobado" en la Tabla estatus

## Problema
La tabla `estatus` tiene un registro duplicado con el nombre "Aprobado". Este script corrige el problema manteniendo el registro con el ID más bajo y actualizando todas las referencias.

## Archivos Disponibles

1. **`eliminar_duplicado_aprobado.sql`** ⭐ **RECOMENDADO** - Script más simple y directo
2. **`corregir_duplicado_estatus_simple.sql`** - Versión alternativa
3. **`corregir_duplicado_estatus_aprobado.sql`** - Versión completa con más verificaciones

## Método Recomendado: Usar `eliminar_duplicado_aprobado.sql`

### Paso 1: Conectarse al servidor
```bash
ssh usuario@servidor
```

### Paso 2: Conectarse a MySQL
```bash
mysql -u usuario_mysql -p
```

### Paso 3: Ejecutar el script
```sql
USE insignia;
source /ruta/completa/eliminar_duplicado_aprobado.sql;
```

O desde la línea de comandos:
```bash
mysql -u usuario_mysql -p insignia < eliminar_duplicado_aprobado.sql
```

## Método Alternativo: Ejecutar comandos directamente

Si prefieres ejecutar los comandos manualmente:

```sql
USE insignia;

-- Ver los duplicados
SELECT id, Nombre_Estatus, Acron_Estatus 
FROM estatus 
WHERE Nombre_Estatus = 'Aprobado' 
ORDER BY id;

-- Identificar IDs
SET @id_original = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');
SET @id_duplicado = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado' AND id != @id_original);

-- Actualizar referencias en T_insignias
UPDATE T_insignias 
SET Estatus = @id_original 
WHERE Estatus = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Actualizar referencias en T_insignias_otorgadas  
UPDATE T_insignias_otorgadas 
SET Id_Estatus = @id_original 
WHERE Id_Estatus = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Eliminar el duplicado
DELETE FROM estatus 
WHERE id = @id_duplicado 
AND @id_duplicado IS NOT NULL;

-- Verificar resultado
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';
```

## Verificación

Después de ejecutar el script, verifica que:

1. Solo quede un registro con "Aprobado":
   ```sql
   SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';
   ```

2. Todos los estatus estén correctos:
   ```sql
   SELECT * FROM estatus ORDER BY id;
   ```

3. No haya referencias huérfanas (opcional):
   ```sql
   SELECT COUNT(*) FROM T_insignias WHERE Estatus NOT IN (SELECT id FROM estatus);
   SELECT COUNT(*) FROM T_insignias_otorgadas WHERE Id_Estatus NOT IN (SELECT id FROM estatus);
   ```

## Notas Importantes

1. **Backup**: Se recomienda hacer un backup antes de ejecutar:
   ```bash
   mysqldump -u usuario_mysql -p insignia estatus > backup_estatus_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **El script mantiene el ID más bajo**: El registro con el ID más bajo (probablemente el original) se mantiene, y todas las referencias al ID más alto se actualizan al ID más bajo antes de eliminar el duplicado.

3. **Sin pérdida de datos**: El script actualiza todas las referencias antes de eliminar, por lo que no se pierden datos.

## Solución de Problemas

### Error: "You can't specify target table for update in FROM clause"
Si recibes este error, usa el script `eliminar_duplicado_aprobado.sql` que usa variables para evitar este problema.

### Error: "Foreign key constraint fails"
Si hay restricciones de clave foránea, primero verifica qué tablas están usando el ID:
```sql
SELECT 'T_insignias' as tabla, Estatus, COUNT(*) 
FROM T_insignias 
WHERE Estatus IN (SELECT id FROM estatus WHERE Nombre_Estatus = 'Aprobado')
GROUP BY Estatus;
```

## Comandos Rápidos (Copia y Pega)

```sql
USE insignia;
SET @id_original = (SELECT MIN(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado');
SET @id_duplicado = (SELECT MAX(id) FROM estatus WHERE Nombre_Estatus = 'Aprobado' AND id != @id_original);
UPDATE T_insignias SET Estatus = @id_original WHERE Estatus = @id_duplicado AND @id_duplicado IS NOT NULL;
UPDATE T_insignias_otorgadas SET Id_Estatus = @id_original WHERE Id_Estatus = @id_duplicado AND @id_duplicado IS NOT NULL;
DELETE FROM estatus WHERE id = @id_duplicado AND @id_duplicado IS NOT NULL;
SELECT * FROM estatus WHERE Nombre_Estatus = 'Aprobado';
```

