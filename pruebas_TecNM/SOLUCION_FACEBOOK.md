# 🚀 Solución para Compartir en Facebook

## ❌ Problema
Facebook no puede acceder a URLs locales (localhost) para mostrar imágenes cuando compartes contenido.

## ✅ Solución Implementada

### 1. Archivos Creados/Modificados:
- `configurar_ngrok_facebook.php` - Configurador de ngrok
- `verificar_ngrok.php` - Verificador de estado
- `test_ngrok.php` - Archivo de prueba
- `imagen_clickeable.php` - Actualizado para usar ngrok

### 2. Pasos para Solucionar:

#### Paso 1: Instalar ngrok
```bash
# Descargar desde: https://ngrok.com/download
# O instalar con chocolatey:
choco install ngrok
```

#### Paso 2: Crear cuenta y configurar
1. Ve a https://dashboard.ngrok.com/signup
2. Crea cuenta gratuita
3. Copia tu token de autenticación
4. Ejecuta: `ngrok config add-authtoken TU_TOKEN`

#### Paso 3: Ejecutar ngrok
```bash
ngrok http 80
```

#### Paso 4: Configurar en el sistema
1. Ve a: `http://localhost/Insignias_TecNM_Funcional/configurar_ngrok_facebook.php`
2. Pega la URL HTTPS que genera ngrok (ej: `https://abc123.ngrok.io`)
3. Guarda la configuración

#### Paso 5: Probar Facebook
1. Ve a: `http://localhost/Insignias_TecNM_Funcional/verificar_ngrok.php`
2. Verifica que ngrok esté funcionando
3. Copia la URL de la insignia
4. Pégala en Facebook - debería mostrar la imagen automáticamente

### 3. URLs Importantes:
- **Configurar ngrok:** `configurar_ngrok_facebook.php`
- **Verificar estado:** `verificar_ngrok.php`
- **Página de insignia:** `imagen_clickeable.php?codigo=TECNM-ITSM-2025-ART-308`

### 4. Notas Importantes:
- La URL de ngrok cambia cada vez que reinicias (versión gratuita)
- Para URLs permanentes necesitas la versión de pago
- Facebook necesita HTTPS para funcionar correctamente
- El sistema ahora detecta automáticamente si estás en localhost y usa ngrok

### 5. Alternativas a ngrok:
- **localtunnel:** `npx localtunnel --port 80`
- **serveo:** `ssh -R 80:localhost:80 serveo.net`

## 🎯 Resultado
Una vez configurado ngrok, Facebook podrá acceder a tu página local y mostrar las imágenes de las insignias cuando compartas el enlace.
