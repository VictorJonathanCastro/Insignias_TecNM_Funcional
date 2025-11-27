# 🔧 Solución al Error de Git: "dubious ownership"

## Error:
```
fatal: detected dubious ownership in repository at '/var/www/html'
```

## ✅ Solución Rápida:

Ejecuta este comando en PuTTY:

```bash
git config --global --add safe.directory /var/www/html
```

Luego intenta de nuevo:

```bash
git pull origin main
```

---

## 🔄 Comandos Completos (Copia y Pega):

```bash
cd /var/www/html
git config --global --add safe.directory /var/www/html
git pull origin main
```

---

## 📝 Explicación:

Este error ocurre porque Git detecta que el repositorio pertenece a un usuario diferente (probablemente `www-data` o `root`) y por seguridad no permite operaciones Git. El comando `git config --global --add safe.directory` le dice a Git que confíe en ese directorio.

---

## ⚠️ Si Aún Tienes Problemas:

Si después de ejecutar el comando anterior aún tienes problemas, puedes cambiar los permisos:

```bash
sudo chown -R devusr01:devusr01 /var/www/html/.git
```

O si prefieres mantener los permisos originales pero permitir que tu usuario trabaje:

```bash
sudo chmod -R 755 /var/www/html/.git
```

