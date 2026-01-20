# Comandos para Actualizar el Servidor Remoto

## Problema
Error: `fatal: not a git repository (or any of the parent directories): .git`

## Solución

### Paso 1: Navegar al directorio correcto del proyecto
```bash
# Generalmente el proyecto está en:
cd /var/www/html/Insignias_TecNM_Funcional
# O en:
cd /var/www/Insignias_TecNM_Funcional
# O en:
cd ~/Insignias_TecNM_Funcional
```

### Paso 2: Verificar que estás en el repositorio correcto
```bash
# Verificar que existe el directorio .git
ls -la | grep .git

# Ver el estado del repositorio
git status
```

### Paso 3: Si el repositorio NO existe, clonarlo
```bash
# Si no existe el repositorio, clonarlo desde GitHub
cd /var/www/html  # O donde quieras clonarlo
git clone https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git
cd Insignias_TecNM_Funcional
```

### Paso 4: Si el repositorio SÍ existe, actualizar
```bash
# Asegurarte de estar en el directorio correcto
cd /ruta/al/proyecto/Insignias_TecNM_Funcional

# Verificar el estado actual
git status

# Obtener los últimos cambios
git pull origin main

# Si hay conflictos, resolverlos primero
# Si hay cambios locales que quieres descartar:
git reset --hard origin/main
```

### Paso 5: Verificar que los archivos se actualizaron
```bash
# Ver los archivos modificados recientemente
ls -lth | head -10

# Verificar que existe ajax_opciones.php (archivo nuevo)
ls -la ajax_opciones.php

# Ver el último commit
git log -1
```

## Comandos Útiles Adicionales

### Ver la ruta actual
```bash
pwd
```

### Buscar el directorio del proyecto
```bash
find /var/www -name "metadatos_formulario.php" -type f 2>/dev/null
find /home -name "metadatos_formulario.php" -type f 2>/dev/null
```

### Verificar permisos
```bash
# Ver permisos del directorio
ls -la

# Si necesitas permisos de escritura
sudo chown -R www-data:www-data /ruta/al/proyecto
sudo chmod -R 755 /ruta/al/proyecto
```

### Si el repositorio está desactualizado
```bash
# Forzar actualización desde remoto
git fetch origin
git reset --hard origin/main
```

## Nota Importante
- Asegúrate de hacer backup antes de hacer `git reset --hard`
- Si tienes cambios locales importantes, guárdalos antes de hacer pull
- Verifica que los permisos de archivos sean correctos después del pull
