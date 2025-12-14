# 📤 Actualizar Código en el Servidor

## Comandos para actualizar desde GitHub:

```bash
# 1. Conectarse al servidor (usando PuTTY o SSH)
# Usuario: devusr01
# IP: 158.23.160.163

# 2. Ir al directorio del proyecto
cd /var/www/html

# 3. Verificar que estás en la rama correcta
git status

# 4. Actualizar desde GitHub
git pull origin main

# 5. Verificar que se actualizaron los archivos
git log --oneline -5
```

## Archivos que se actualizaron:

- ✅ `metadatos_formulario.php` - Flujo: primero firmar, luego registrar
- ✅ `firmar_certificado.php` - Guarda firma en sesión y redirige al formulario
- ✅ Tabla `insigniasotorgadas` - Ahora incluye campos para firma digital (solo sello, no archivos)

## Verificar cambios:

```bash
# Ver los últimos commits
git log --oneline -3

# Verificar que los archivos están actualizados
ls -la metadatos_formulario.php firmar_certificado.php
```

## Si hay conflictos:

```bash
# Si hay cambios locales que no quieres perder
git stash

# Luego hacer pull
git pull origin main

# Si necesitas recuperar cambios locales
git stash pop
```

