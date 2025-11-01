# 📤 Guía para Subir Proyecto al Servidor usando Archivos SSH

## 📋 Archivos que tienes en `C:\Users\vc556\Desktop\llaves\`:
- `priv_insignias.ppk` - Clave privada PuTTY (formato .ppk para autenticación SSH)
- `ssh_insignias` - Clave privada en formato OpenSSH (RSA) - alternativa si necesitas formato OpenSSH

**Ruta completa de la clave PuTTY:**
`C:\Users\vc556\Desktop\llaves\priv_insignias.ppk`

---

## ⚡ ¿Cuál método usar? Recomendación rápida

### 🌟 **MÁS RECOMENDADO: GitHub + Git Clone** (Método más fácil)

**✅ Este es el método MÁS FÁCIL y RECOMENDADO:**

**Ventajas:**
- 🚀 **Mucho más fácil**: Solo subes a GitHub y clonas en el servidor
- 📦 **Un solo comando**: `git clone` en el servidor
- 🔄 **Fácil de actualizar**: Solo haces `git pull` para actualizar
- ✅ **Versionado**: Tienes respaldo y control de versiones
- 🔒 **Seguro**: No necesitas transferir archivos manualmente
- 💾 **Backup automático**: Tu código está en GitHub

**Pasos simples:**
1. **Subir proyecto a GitHub** (desde tu computadora)
2. **Clonar en el servidor** (un solo comando)
3. **Configurar y listo**

**Ideal para**: Cualquiera que quiera el método más fácil y profesional

---

### 🎯 **Alternativa: PuTTY/PSCP** (Método 2)

**✅ Ventajas:**
- **Más fácil en Windows**: Herramienta gráfica intuitiva
- **Sin conversión**: Usa directamente el archivo `.ppk` que ya tienes
- **Todo incluido**: PuTTY incluye todas las herramientas necesarias
- **Interfaz gráfica**: PuTTY te permite ver y guardar sesiones fácilmente
- **Muy común**: Es el estándar en Windows para SSH

**❌ Desventajas:**
- Requiere instalar PuTTY (pero es rápido y gratuito)

**Ideal para**: Usuarios en Windows que prefieren herramientas gráficas

---

### 💻 **Alternativa: SSH OpenSSH** (Método 4)

**✅ Ventajas:**
- **Ya viene con Windows 10/11**: No necesitas instalar nada extra
- **Más estándar**: Es el mismo SSH usado en Linux/Mac
- **Directo desde PowerShell**: Comandos simples

**❌ Desventajas:**
- Puede requerir habilitar OpenSSH en Windows
- Menos amigable para principiantes
- Necesitas usar el archivo `ssh_insignias` (formato diferente)

**Ideal para**: Usuarios cómodos con línea de comandos, o si prefieres no instalar software extra

---

### 🏆 **Mi recomendación para ti:**

**🌟 Usa GitHub + Git Clone** porque:
1. **Es el método MÁS FÁCIL** - No necesitas transferir archivos manualmente
2. **Más profesional** - Tienes control de versiones y backup
3. **Fácil de actualizar** - Solo `git pull` cuando cambies algo
4. **Un solo comando** - `git clone` y ya está en el servidor
5. **Más rápido** - No necesitas subir archivos uno por uno

**Pasos rápidos con GitHub:**
1. Sube tu proyecto a GitHub (una vez)
2. Conéctate al servidor por SSH
3. Ejecuta `git clone` en el servidor
4. Configura y listo

**O si prefieres método tradicional:**
- Usa PuTTY/PSCP (Método 2) para transferir archivos directamente

---

## 🔍 Paso 1: Obtener información del servidor

### 🌐 Dominio del proyecto
**Dominio configurado**: `InsigniasTecNM`

El sistema estará disponible en:
- `http://InsigniasTecNM/`
- `https://InsigniasTecNM/` (después de configurar SSL)

### 📋 Información necesaria para conectarte:
- **Dominio**: `InsigniasTecNM` (si el DNS está configurado - ⚠️ Probablemente NO está configurado aún)
- **IP del servidor**: `❌ FALTA` (necesitas pedirla a quien te dio acceso)
- **Usuario SSH**: `devusr01` ✅ (ya lo tienes)
- **Puerto SSH**: `22` ✅ (estándar)

**⚠️ Situación actual:** 
- ✅ Tienes las llaves SSH (`priv_insignias.ppk` y `ssh_insignias`)
- ✅ Tienes el usuario (`devusr01`)
- ✅ La máquina virtual se solicitó en el correo con todas las especificaciones
- ⏳ **La MV probablemente aún NO está creada** o está en proceso
- ❌ **Falta la IP del servidor** - Te la darán cuando la máquina virtual esté lista y configurada

**📝 Pasos siguientes:**
1. **Espera** la notificación de que la máquina virtual está lista
2. **Solicita la IP pública** del servidor
3. **Usa esta guía** para conectarte y subir el proyecto una vez que tengas la IP

---

## ✅ Preparación mientras esperas la IP del servidor

**Aunque no tengas la IP aún, puedes preparar TODO para cuando esté lista:**

### 1. 📥 Instalar herramientas necesarias

**Instala PuTTY ahora** (para cuando tengas la IP):
- ✅ Descarga: https://www.putty.org/
- ✅ Instala normalmente
- ✅ Abre PuTTYgen y carga `priv_insignias.ppk` para verificar que funciona

**Opcional - Editores de texto:**
- ✅ Notepad++: https://notepad-plus-plus.org/downloads/
- ✅ Visual Studio Code: https://code.visualstudio.com/

### 2. 🔍 Verificar tus archivos de llaves

**Abre PuTTYgen y verifica tus llaves:**
1. Abre **PuTTYgen**
2. Clic en **Load**
3. Selecciona `C:\Users\vc556\Desktop\llaves\priv_insignias.ppk`
4. Verifica que se carga correctamente
5. Anota el **fingerprint** de la llave (para comparar con el servidor después)

### 3. 📦 Preparar tu proyecto para subir

**Si vas a usar GitHub (Método 1 - Recomendado):**
- ✅ Crea tu cuenta en GitHub (si no la tienes)
- ✅ Prepara el archivo `.gitignore` (ver Método 1 más abajo)
- ✅ Revisa qué archivos NO debes subir

**Si vas a usar PSCP/SFTP (Método 2):**
- ✅ Revisa qué archivos NO debes subir
- ✅ Prepara la carpeta lista para transferir

**Archivos que NO debes subir:**
- Archivos de prueba: `prueba_*.php`, `test_*.php`, `debug_*.php`
- Backups: `*.bak`, `*.zip`, `*.sql` (excepto `BD/backup_sistema_funcional.sql`)
- Archivos temporales: `*.tmp`, `*.log`
- Configuración local: `conexion.php` (lo configurarás en el servidor)
- Carpetas eliminadas: `certificados/`, `firmas_digitales/*.html`

### 4. ✅ Verificar que tu proyecto funciona localmente

**Asegúrate de que el proyecto funciona en XAMPP antes de subirlo:**

1. ✅ Prueba que el proyecto se ve correctamente en: `http://localhost/Insignias_TecNM_Funcional/`
2. ✅ Verifica que la base de datos local funciona
3. ✅ Revisa los logs de errores si hay problemas

### 5. 📝 Preparar información para el servidor

**Cuando tengas la IP, necesitarás:**

1. **IP pública del servidor** (te la darán)
2. **Credenciales de base de datos del servidor** (pregunta si no las tienes):
   - Usuario de MySQL
   - Contraseña de MySQL
   - Nombre de la base de datos (probablemente `insignia`)

3. **Revisa el archivo `conexion.php`** local para saber qué necesitas configurar:
   ```php
   $servidor = "localhost";     // Esto será "localhost" en el servidor
   $usuario = "???";             // Pregunta el usuario del servidor
   $password = "???";            // Pregunta la contraseña del servidor
   $bd = "insignia";             // Probablemente este nombre
   $puerto = 3306;               // Puerto estándar de MySQL
   ```

### 6. 📚 Revisar las guías completas

**Lee estas guías mientras esperas:**

- ✅ `GUIA_SUBIR_PROYECTO_SSH.md` - Esta guía (ya la estás viendo)
- ✅ `GUIA_DESPLIEGUE_UBUNTU.md` - Guía completa de despliegue
- ✅ `ESPECIFICACIONES_TECNICAS_MV.md` - Especificaciones de la máquina virtual

### 7. 🧪 Probar comandos PSCP localmente (opcional)

**Puedes verificar que PSCP funciona** (aunque no te conectes aún):

```powershell
# Verificar que PSCP está instalado
"C:\Program Files\PuTTY\pscp.exe" -V

# Debería mostrar la versión de PSCP
```

---

## 🎯 Resumen: Lo que tienes vs. Lo que falta

### ✅ Lo que SÍ tienes (y es suficiente):
- ✅ Llaves SSH (`priv_insignias.ppk` y `ssh_insignias`)
- ✅ Usuario SSH (`devusr01`)
- ✅ Puerto SSH (`22`)
- ✅ Tu proyecto local completo
- ✅ Guías detalladas

### ❌ Lo que falta (y te lo darán):
- ❌ **IP pública del servidor** (te la darán cuando la MV esté lista)

**Conclusión:** Tienes TODO lo necesario. Solo falta esperar la IP del servidor para poder conectarte.

### ⚠️ IMPORTANTE: Si obtienes error "Host does not exist"
Si al intentar conectarte con `InsigniasTecNM` obtienes el error **"Host does not exist"**, significa que:
- El DNS aún no está configurado en el servidor
- Debes usar la **IP del servidor directamente** en lugar del dominio

### 🔍 ¿No tienes la IP del servidor?
Si solo te dieron los archivos `priv_insignias.ppk` y `ssh_insignias`, pero **NO te dieron la IP**, es porque:

**📋 Situación actual:**
- ✅ Te dieron las **llaves SSH** para acceso al servidor
- ✅ El correo solicitó la **creación de la máquina virtual** con estas especificaciones:
  - CPU: 8 vCPU (3.0 GHz)
  - RAM: 16GB
  - Disco: 500GB
  - SO: Ubuntu Server 22.04 LTS
  - Puertos: 22 (SSH), 80 (HTTP), 443 (HTTPS)
  - Dominio: `InsigniasTecNM`
- ⏳ **La máquina virtual probablemente aún NO está creada** o está en proceso
- ❌ Por eso **no tienes la IP aún** - te la darán cuando la MV esté lista

**📧 Qué hacer ahora:**
1. **Espera** a que te notifiquen que la máquina virtual está lista
2. **Pide la IP pública del servidor** una vez que la MV esté creada

**Mensaje sugerido para solicitar la IP:**
> "Hola, siguiendo la solicitud de la MV para el proyecto InsigniasTecNM.  
> Tengo las llaves SSH (`priv_insignias.ppk`) y el usuario (`devusr01`).  
> Por favor, necesito la **IP pública del servidor** una vez que la máquina virtual esté lista para poder conectarme y subir el proyecto."

**Lo que necesitas específicamente:**
- **IP pública del servidor** (te la darán cuando la MV esté creada)
- Confirmar el **puerto SSH**: `22` (según el correo, este puerto debe estar abierto)

### 📄 Revisar archivo ssh_insignias

Para ver mejor el contenido de tus archivos de texto (como `ssh_insignias`), puedes usar:

**📝 Editores de texto recomendados:**

1. **Notepad++** (Gratuito, recomendado para Windows)
   - Descarga: https://notepad-plus-plus.org/downloads/
   - Útil para: Ver archivos de texto, código, configuraciones
   - Ventaja: Resalta sintaxis, fácil de usar

2. **Visual Studio Code** (Gratuito, muy completo)
   - Descarga: https://code.visualstudio.com/
   - Útil para: Ver código, archivos de configuración, editar múltiples archivos
   - Ventaja: Muchas extensiones, muy popular

3. **Bloc de notas** (Ya viene con Windows)
   - Ya lo tienes instalado
   - Útil para: Ver archivos simples de texto
   - Limitación: No tiene resaltado de sintaxis

**🔍 Cómo revisar tus archivos:**

1. Abre `ssh_insignias` con Notepad++ o VS Code
2. **Lo que verás**:
   - `-----BEGIN RSA PRIVATE KEY-----` (inicio de la clave privada)
   - Líneas codificadas en Base64 (los datos de la clave)
   - `-----END RSA PRIVATE KEY-----` (fin de la clave privada)
3. **Esto confirma que**:
   - ✅ El archivo es una clave privada RSA válida
   - ✅ Está en formato OpenSSH (PEM)
   - ❌ **NO contiene información del servidor** (IP, dominio, etc.)
   - ❌ Solo es la clave privada para autenticación SSH

**📝 Conclusión:**
- El archivo `ssh_insignias` es **solo la clave privada** en formato OpenSSH
- El archivo `priv_insignias.ppk` es la **misma clave privada** en formato PuTTY
- **Ambos archivos son la misma llave, solo en formatos diferentes**
- **No contienen información del servidor** - necesitas la IP del servidor que te darán cuando la MV esté lista

---

## 🌟 Método 1: Usando GitHub + Git Clone (MÁS RECOMENDADO) ⭐

**Este es el método MÁS FÁCIL y RECOMENDADO para subir tu proyecto al servidor.**

### 📥 Paso 1: Subir tu proyecto a GitHub

**Si ya tienes el proyecto en GitHub, salta al Paso 2.**

#### 1.1. Crear repositorio en GitHub

1. Ve a: https://github.com/
2. Inicia sesión o crea una cuenta
3. Clic en **"New repository"** (botón verde)
4. Nombre del repositorio: `Insignias_TecNM_Funcional`
5. Descripción: "Sistema de Insignias Digitales TecNM"
6. Selecciona **"Public"** o **"Private"** (como prefieras)
7. **NO marques** "Initialize with README" (tu proyecto ya tiene archivos)
8. Clic en **"Create repository"**

#### 1.2. Inicializar Git en tu proyecto local

```powershell
# Desde PowerShell, en la carpeta del proyecto
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

# Inicializar Git (si aún no lo tienes)
git init

# Crear archivo .gitignore (para excluir archivos innecesarios)
# Ver sección 1.3 abajo
```

#### 1.3. Crear archivo `.gitignore`

Crea un archivo `.gitignore` en la raíz del proyecto para excluir archivos innecesarios:

```powershell
# Crear archivo .gitignore
New-Item -Path ".gitignore" -ItemType File
```

Abre `.gitignore` con Notepad++ o VS Code y agrega:

```
# Archivos de configuración local (NO subir)
conexion.php

# Archivos temporales
*.tmp
*.bak
*.log
*.swp
*~

# Archivos de prueba
prueba_*.php
test_*.php
debug_*.php
verificar_*.php

# Backups
*.zip
*.sql
BD/*.sql
!BD/backup_sistema_funcional.sql

# Carpetas eliminadas
certificados/
firmas_digitales/*.html

# Archivos del sistema
.DS_Store
Thumbs.db
.vscode/
.idea/

# Logs
logs/
*.log

# Uploads locales
uploads/*
!uploads/.gitkeep
```

#### 1.4. Subir proyecto a GitHub

```powershell
# Agregar todos los archivos
git add .

# Hacer commit inicial
git commit -m "Initial commit - Sistema de Insignias Digitales TecNM"

# Agregar el repositorio remoto
git remote add origin https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git

# Subir al repositorio
git branch -M main
git push -u origin main
```

**Nota**: Si te pide autenticación, puedes usar:
- **Personal Access Token** (recomendado)
- O **GitHub Desktop** (más fácil para principiantes)

---

### 📥 Paso 2: Clonar el proyecto en el servidor

**Una vez que tengas la IP del servidor y esté lista la máquina virtual:**

#### 2.1. Conectarse al servidor por SSH

```bash
# Usando PuTTY (configura la sesión con la clave privada)
# O desde PowerShell:
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@IP_SERVIDOR
```

#### 2.2. Instalar Git en el servidor (si no está instalado)

```bash
# Actualizar sistema
sudo apt update

# Instalar Git
sudo apt install git -y

# Verificar instalación
git --version
```

#### 2.3. Clonar el repositorio en el servidor

```bash
# Ir al directorio web
cd /var/www

# Clonar tu repositorio de GitHub
sudo git clone https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git

# Si tu repositorio es privado, necesitarás configurar autenticación
# Opción A: Usar HTTPS con token
# Opción B: Configurar SSH key en GitHub (más seguro)
```

#### 2.4. Configurar permisos

```bash
# Cambiar propietario
sudo chown -R www-data:www-data /var/www/Insignias_TecNM_Funcional

# Dar permisos correctos
cd /var/www/Insignias_TecNM_Funcional
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Permisos especiales para directorios de escritura
sudo chmod 775 imagen/
sudo mkdir -p uploads logs
sudo chmod 775 uploads logs
```

#### 2.5. Configurar conexión.php (crear desde plantilla)

```bash
# Crear conexion.php desde una plantilla
cd /var/www/Insignias_TecNM_Funcional
sudo nano conexion.php
```

Agrega tu configuración de base de datos del servidor:

```php
<?php
$servidor = "localhost";
$usuario = "tu_usuario_bd";
$password = "tu_password_bd";
$bd = "insignia";
$puerto = 3306;

// ... resto del código
?>
```

#### 2.6. Importar base de datos

```bash
# Ir a la carpeta BD
cd /var/www/Insignias_TecNM_Funcional/BD

# Importar estructura
sudo mysql -u tu_usuario_bd -p insignia < backup_sistema_funcional.sql
```

---

### 🔄 Actualizar el proyecto en el servidor (cuando hagas cambios)

**Cuando actualices código en GitHub, actualízalo en el servidor con un solo comando:**

```bash
# Conectarte al servidor
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@IP_SERVIDOR

# Ir al directorio del proyecto
cd /var/www/Insignias_TecNM_Funcional

# Actualizar desde GitHub
sudo git pull origin main

# Si hay conflictos o cambios locales, puedes hacer:
sudo git fetch origin
sudo git reset --hard origin/main
```

**¡Es así de fácil!** 🎉

---

## 🔧 Método 2: Usando PuTTY y PSCP (Método tradicional)

PuTTY incluye todas las herramientas necesarias para conectarse por SSH y transferir archivos.

### 1.1. Descargar PuTTY

**📥 Descargar PuTTY:**
- **Sitio web**: https://www.putty.org/
- **Descarga directa**: https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html
- **Instalación**: Ejecuta el instalador y sigue los pasos

**🛠️ Herramientas que incluye PuTTY:**
- **PuTTY** - Cliente SSH (para conectarte al servidor)
- **PSCP** - Para transferir archivos (como SCP)
- **PSFTP** - Cliente SFTP interactivo
- **PuTTYgen** - Generador/visor de llaves SSH (**muy útil para ver tus archivos**)

### 1.1.1. 🔍 Usar PuTTYgen para VER y analizar tus archivos de llaves

**PuTTYgen** es el programa que necesitas para **abrir y ver información detallada** de tus archivos `.ppk`:

#### 📥 Cómo usar PuTTYgen:

1. **Instala PuTTY** primero (si no lo has hecho):
   - Descarga desde: https://www.putty.org/
   - PuTTYgen viene incluido automáticamente

2. **Abre PuTTYgen**:
   - Busca **"PuTTYgen"** en el menú de inicio de Windows
   - O ve a: `C:\Program Files\PuTTY\puttygen.exe`

3. **Carga tu archivo `.ppk`**:
   - En PuTTYgen, haz clic en el botón **"Load"**
   - En el diálogo, cambia el filtro de "PuTTY Private Key Files (*.ppk)" a **"All Files (*.*)"**
   - Navega a: `C:\Users\vc556\Desktop\llaves\`
   - Selecciona `priv_insignias.ppk`
   - Si pide contraseña, ingrésala (o déjala vacía si no tiene)

4. **Verás información detallada** de tu llave:
   - ✅ **Tipo de clave**: RSA, DSA, ECDSA, etc.
   - ✅ **Número de bits**: 1024, 2048, 4096, etc.
   - ✅ **Public key**: La clave pública asociada (puedes copiarla)
   - ✅ **Key fingerprint**: Identificador único de la clave
   - ✅ **Comment**: Comentario o descripción (si tiene)
   - ✅ **Key passphrase**: Si está protegida con contraseña

**📝 Qué puedes hacer con PuTTYgen:**
- ✅ **Verificar** que tu llave está correcta
- ✅ **Ver el fingerprint** para comparar con el servidor
- ✅ **Copiar la clave pública** si la necesitas
- ✅ **Convertir formatos**: Exportar a OpenSSH, etc.
- ✅ **Generar nuevas llaves** si lo necesitas
- ✅ **Cambiar la contraseña** de la llave (si tiene)

**💡 Consejo:** PuTTYgen es **el programa ideal** para trabajar con archivos `.ppk` y ver toda su información.

### 1.2. Configurar sesión SSH en PuTTY

**⚠️ IMPORTANTE**: Si obtienes el error "Host does not exist", significa que el dominio `InsigniasTecNM` no está configurado en el DNS aún. En ese caso, **usa la IP del servidor directamente**.

1. Abre **PuTTY**
2. En **Session**:
   - **Host Name (or IP address)**: 
     - Si el DNS está configurado: `InsigniasTecNM`
     - Si NO está configurado: **Usa la IP del servidor** (ejemplo: `192.168.1.100` o la IP que te hayan dado)
   - **Port**: `22`
   - **Connection type**: SSH
3. En el panel izquierdo, ve a **Connection** → **SSH** → **Auth**
   - Marca **"Allow agent forwarding"** (opcional)
4. Ve a **Connection** → **SSH** → **Credentials**
   - En **"Private key file for authentication"**, haz clic en **Browse**
   - Selecciona: `C:\Users\vc556\Desktop\llaves\priv_insignias.ppk`
5. Regresa a **Session**
   - En **"Saved Sessions"**, escribe: `InsigniasTecNM`
   - Haz clic en **Save**
6. Haz clic en **Open** para conectarte

### 1.3. Subir archivos usando PSCP (desde PowerShell o CMD)

**PSCP** viene incluido con PuTTY y se encuentra en la carpeta de instalación (generalmente `C:\Program Files\PuTTY\`)

#### Opción A: Agregar PSCP al PATH (recomendado)

1. Copia `pscp.exe` a una carpeta en tu PATH o agrégalo al PATH del sistema
2. O usa la ruta completa al ejecutable

#### Opción B: Usar desde PowerShell/CMD

```powershell
# Navegar a la carpeta del proyecto
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

# Subir todos los archivos usando PSCP
"C:\Program Files\PuTTY\pscp.exe" -i "C:\Users\vc556\Desktop\llaves\priv_insignias.ppk" -r * devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

#### Opción C: Usar PSFTP (interfaz interactiva)

```powershell
# Iniciar PSFTP
"C:\Program Files\PuTTY\psftp.exe" -i "C:\Users\vc556\Desktop\llaves\priv_insignias.ppk" devusr01@InsigniasTecNM

# Una vez conectado, usar comandos:
cd /var/www
mkdir Insignias_TecNM_Funcional
cd Insignias_TecNM_Funcional
lcd C:\xampp\htdocs\Insignias_TecNM_Funcional
put -r *
exit
```

### 1.4. Subir carpeta completa (método fácil)

```powershell
# Desde PowerShell (como Administrador)
cd C:\xampp\htdocs

# Subir toda la carpeta
"C:\Program Files\PuTTY\pscp.exe" -i "C:\Users\vc556\Desktop\llaves\priv_insignias.ppk" -r Insignias_TecNM_Funcional devusr01@InsigniasTecNM:/var/www/
```

---

## 🪟 Método 3: Usando WinSCP (Alternativa - Windows)

Si prefieres una interfaz gráfica similar a FileZilla:

### 3.1. Descargar WinSCP
- Descarga desde: https://winscp.net/eng/download.php
- Instala normalmente

### 3.2. Conectar con WinSCP

1. Abre WinSCP
2. **Nuevo sitio**:
   - **Protocolo**: SFTP
   - **Nombre de host**: `InsigniasTecNM` (o IP del servidor)
   - **Puerto**: 22
   - **Nombre de usuario**: `devusr01`
   - **Contraseña**: (déjala vacía si usas clave privada)
3. Clic en **Avanzado...** → **Autenticación**
4. En **Clave privada**, selecciona `C:\Users\vc556\Desktop\llaves\priv_insignias.ppk`
5. **OK** → **Guardar** → **Login**

### 3.3. Subir archivos

1. En el lado **IZQUIERDO**: Navega a tu proyecto local
   - `C:\xampp\htdocs\Insignias_TecNM_Funcional`
2. En el lado **DERECHO**: Navega a `/var/www/` o donde quieras subir
3. Selecciona **todos los archivos** de la carpeta local
4. **Arrastra** o clic derecho → **Subir**

---

## 🌐 Método 4: Usando FileZilla (Windows/Mac/Linux)

### 3.1. Descargar FileZilla
- Descarga desde: https://filezilla-project.org/
- Instala normalmente

### 3.2. Conectar con FileZilla

1. Abre FileZilla
2. Clic en **Archivo** → **Gestor de sitios**
3. **Nuevo sitio**:
   - **Protocolo**: SFTP - SSH File Transfer Protocol
   - **Host**: `InsigniasTecNM` (o IP del servidor)
   - **Puerto**: 22
   - **Tipo de acceso**: Clave de archivo
   - **Usuario**: `devusr01`
   - **Archivo de clave**: Selecciona `C:\Users\vc556\Desktop\llaves\priv_insignias.ppk`
4. **Conectar**

### 4.3. Subir archivos

1. Panel **Local** (izquierda): Tu carpeta del proyecto
2. Panel **Remoto** (derecha): `/var/www/`
3. Arrastra la carpeta `Insignias_TecNM_Funcional` al servidor

---

## 💻 Método 5: Usando PowerShell (Windows 10/11)

### 4.1. Convertir clave PuTTY a formato OpenSSH

```powershell
# Instalar PuTTY tools (si no lo tienes)
# Descargar desde: https://www.putty.org/

# Convertir clave usando PuTTYgen desde línea de comandos
# O usar WinSCP/PuTTYgen GUI para convertir

# Copiar la clave convertida a: C:\Users\TuUsuario\.ssh\id_rsa
```

### 4.2. Establecer permisos correctos

```powershell
# En PowerShell como Administrador
icacls "C:\Users\TuUsuario\.ssh\id_rsa" /inheritance:r
icacls "C:\Users\TuUsuario\.ssh\id_rsa" /grant:r "%username%:R"
```

### 4.3. Subir proyecto usando SCP

```powershell
# Navegar a la carpeta del proyecto
cd C:\xampp\htdocs\Insignias_TecNM_Funcional

# Subir carpeta completa al servidor usando formato OpenSSH
scp -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" -r * devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

### 4.4. O usar SCP con OpenSSH (clave convertida)

```powershell
# Usar la clave en formato OpenSSH directamente
scp -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" -r * devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

---

## 🐧 Método 6: Usando línea de comandos Linux/Mac

### 5.1. Convertir clave PuTTY (si es necesario)

```bash
# Instalar putty-tools
sudo apt install putty-tools

# Convertir clave PuTTY a OpenSSH
puttygen priv_insignias -O private-openssh -o ~/.ssh/id_rsa

# Establecer permisos correctos
chmod 600 ~/.ssh/id_rsa
```

### 5.2. Subir proyecto

```bash
# Desde tu computadora local
cd /ruta/a/tu/proyecto/Insignias_TecNM_Funcional

# Subir usando SCP
scp -i ~/.ssh/id_rsa -r * devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/

# O si prefieres usar rsync (más eficiente)
rsync -avz -e "ssh -i ~/.ssh/id_rsa" ./ devusr01@InsigniasTecNM:/var/www/Insignias_TecNM_Funcional/
```

---

## 🔐 Paso 2: Configurar permisos en el servidor

Después de subir los archivos, conecta al servidor y configura permisos:

### 2.1. Conectarse al servidor

```bash
# Usando PuTTY GUI (recomendado para Windows):
# Abre PuTTY y configura la sesión con la clave privada

# O usando SSH desde PowerShell/CMD con OpenSSH:
ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@InsigniasTecNM

# O si usas Linux/Mac:
ssh -i ~/llaves/ssh_insignias devusr01@InsigniasTecNM
```

### 2.2. Configurar permisos

```bash
# Ir al directorio del proyecto
cd /var/www/Insignias_TecNM_Funcional

# Cambiar propietario
sudo chown -R www-data:www-data .

# Dar permisos correctos
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Permisos especiales para directorios de escritura
sudo chmod 775 imagen/
sudo mkdir -p uploads logs
sudo chmod 775 uploads logs
```

---

## 📦 Paso 3: Excluir archivos innecesarios antes de subir

Antes de subir, puedes crear un archivo `.gitignore` o simplemente NO subir:

- Archivos temporales de PHP
- Archivos de pruebas (`prueba_*.php`, `test_*.php`)
- Backups locales
- Archivos de configuración local (`conexion.php` - lo configurarás en el servidor)

### Lista sugerida de archivos a NO subir:

```
*.bak
*.tmp
*.log
prueba_*.php
test_*.php
debug_*.php
*.zip
certificados/*  (esta carpeta ya fue eliminada)
firmas_digitales/*.html  (archivos temporales)
```

---

## ⚙️ Paso 4: Configurar conexión en el servidor

Después de subir, edita `conexion.php` en el servidor:

```bash
# Conectarse al servidor
ssh -i priv_insignias usuario@ip_servidor

# Editar archivo
cd /var/www/Insignias_TecNM_Funcional
sudo nano conexion.php
```

Configurar con las credenciales de tu base de datos del servidor:

```php
<?php
$servidor = "localhost";
$usuario = "tu_usuario_bd";
$password = "tu_password_bd";
$bd = "insignia";
$puerto = 3306;

// ... resto del código
?>
```

---

## 🗄️ Paso 5: Importar base de datos

```bash
# En el servidor
cd /var/www/Insignias_TecNM_Funcional/BD

# Importar estructura
sudo mysql -u tu_usuario_bd -p insignia < backup_sistema_funcional.sql
```

---

## ✅ Verificación final

### Verificar que los archivos se subieron correctamente:

```bash
# En el servidor
ls -la /var/www/Insignias_TecNM_Funcional/

# Verificar permisos
ls -la /var/www/Insignias_TecNM_Funcional/imagen/
```

### Acceder desde navegador:

- **Dominio**: `http://InsigniasTecNM/` o `https://InsigniasTecNM/`
- **IP alternativa**: `http://tu-ip-servidor/` (si el DNS no está configurado aún)

---

## 🆘 Solución de Problemas

### Error: "Permission denied (publickey)"

**Solución:**
- Verifica que el archivo `priv_insignias` tenga los permisos correctos
- En Linux/Mac: `chmod 600 priv_insignias`
- En Windows: Usa las herramientas GUI (WinSCP, FileZilla)

### Error: "Host key verification failed"

**Solución:**
```bash
# Limpiar known_hosts
ssh-keygen -R ip_servidor
```

### Error: "Connection refused"

**Solución:**
- Verifica que el servidor esté encendido
- Verifica que el puerto SSH (22) esté abierto
- Verifica la IP o dominio

### La carpeta se subió pero no funciona

**Solución:**
- Verifica permisos: `sudo chown -R www-data:www-data /var/www/Insignias_TecNM_Funcional`
- Verifica que Apache tenga acceso
- Revisa logs: `sudo tail -f /var/log/apache2/error.log`

---

## 📝 Resumen rápido - Dos métodos disponibles

### 🌟 Método 1: GitHub + Git Clone (MÁS RECOMENDADO) ⭐

1. ✅ Subir proyecto a GitHub desde tu computadora:
   ```powershell
   cd C:\xampp\htdocs\Insignias_TecNM_Funcional
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git
   git push -u origin main
   ```

2. ✅ Conectarse al servidor por SSH (cuando tengas la IP):
   ```bash
   ssh -i "C:\Users\vc556\Desktop\llaves\ssh_insignias" devusr01@IP_SERVIDOR
   ```

3. ✅ Clonar el proyecto en el servidor:
   ```bash
   cd /var/www
   sudo git clone https://github.com/VictorJonathanCastro/Insignias_TecNM_Funcional.git
   sudo chown -R www-data:www-data /var/www/Insignias_TecNM_Funcional
   ```

4. ✅ Configurar `conexion.php` con credenciales del servidor
5. ✅ Importar base de datos desde `BD/backup_sistema_funcional.sql`
6. ✅ Verificar acceso desde navegador: `http://InsigniasTecNM/`

**Para actualizar en el futuro:**
```bash
cd /var/www/Insignias_TecNM_Funcional
sudo git pull origin main
```

---

### 🔧 Método 2: PuTTY/PSCP (Método tradicional)

1. ✅ Descargar PuTTY (incluye PSCP)
2. ✅ Configurar sesión SSH en PuTTY con la clave privada
3. ✅ Subir carpeta usando PSCP desde PowerShell
4. ✅ Configurar permisos en el servidor
5. ✅ Configurar `conexion.php` e importar base de datos
6. ✅ Verificar acceso desde navegador

---

**¿Necesitas ayuda? Revisa la guía completa en `GUIA_DESPLIEGUE_UBUNTU.md`**

