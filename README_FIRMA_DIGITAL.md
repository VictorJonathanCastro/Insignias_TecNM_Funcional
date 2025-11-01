# 🔐 Sistema de Firma Digital Real - TecNM

## 📋 Descripción

Sistema completo de firma digital real para Insignias Digitales del Tecnológico Nacional de México. Implementa firmas digitales usando certificados .cer, claves privadas .key y contraseñas, generando firmas en formato Base64 como en el ejemplo proporcionado.

## 🚀 Características Principales

### ✅ Firma Digital Real
- **Certificados .cer**: Certificados públicos para verificación
- **Claves privadas .key**: Claves privadas para firmar
- **Contraseñas**: Protección de claves privadas
- **Algoritmo SHA-256**: Estándar criptográfico internacional
- **Formato Base64**: Codificación estándar para firmas

### ✅ Sistema Integrado
- **Tabla responsable_emision**: Almacena firmas digitales
- **Generación automática**: Firmas al crear insignias
- **Verificación pública**: Validación de autenticidad
- **Interfaz administrativa**: Gestión de certificados

### ✅ Seguridad
- **Autenticidad**: Solo quien tiene la clave privada puede firmar
- **Integridad**: Cualquier cambio invalida la firma
- **Trazabilidad**: Registro completo de quién y cuándo firmó
- **Verificabilidad**: Validación sin necesidad de clave privada

## 📁 Archivos del Sistema

### 🔧 Archivos Principales
- `firma_digital_real.php` - Clase principal del sistema
- `integracion_firma_digital.php` - Integración con sistema existente
- `gestion_firma_digital_real.php` - Interfaz de administración
- `verificar_firma_digital_real.php` - Verificador público
- `prueba_firma_digital_completa.php` - Pruebas del sistema

### 📂 Directorios
- `certificados/` - Almacena certificados .cer y .key
- `firmas_digitales/` - Archivos de firmas generadas

## 🛠️ Instalación y Configuración

### 1. Preparar Certificados
```bash
# Crear directorio para certificados
mkdir certificados/

# Colocar archivos de certificado
# certificados/responsable.cer
# certificados/responsable.key
```

### 2. Configurar Base de Datos
El sistema crea automáticamente la tabla `responsable_emision` con las columnas:
- `firma_digital_base64` - Firma en formato Base64
- `certificado_path` - Ruta al certificado .cer
- `fecha_generacion` - Fecha de generación de la firma

### 3. Permisos de Archivos
```bash
chmod 755 certificados/
chmod 644 certificados/*.cer
chmod 600 certificados/*.key
```

## 📖 Uso del Sistema

### 🔐 Generar Firma Digital

1. **Acceder a la gestión**:
   - Ir a `gestion_firma_digital_real.php`
   - Tab "Generar Firma"

2. **Completar formulario**:
   - Nombre del responsable
   - Cargo del responsable
   - Ruta del certificado (.cer)
   - Ruta de la clave privada (.key)
   - Contraseña del certificado

3. **Generar firma**:
   - Hacer clic en "Generar Firma Digital Real"
   - El sistema genera la firma en Base64
   - Se guarda automáticamente en la base de datos

### 🔍 Verificar Firma Digital

1. **Acceder al verificador**:
   - Ir a `verificar_firma_digital_real.php`

2. **Completar datos**:
   - Texto original que se firmó
   - Firma digital en Base64
   - Ruta del certificado (.cer)

3. **Verificar**:
   - Hacer clic en "Verificar Firma Digital"
   - El sistema valida la autenticidad

### 🏆 Generar Insignia con Firma

1. **Usar el sistema integrado**:
   - El sistema genera automáticamente firmas al crear insignias
   - Se integra con la tabla `responsable_emision`

2. **Resultado**:
   - Insignia con firma digital visible
   - Datos de verificación ocultos
   - Código QR para verificación rápida

## 📋 Ejemplo de Uso

### Texto a Firmar
```
Certificado de Insignia Digital - TecNM
Alumno: Jonathan Castro
Insignia: Desarrollador Destacado
Fecha: 22/10/2025
```

### Firma Digital (Base64)
```
T0lJQTJEVEhZb0E3dGdJUElGRFZVZ2NhQkZKQ1JjMG1uT1FJa1dhV3dXanlDNE1DMG5PZmpFdUts
U2VrY3lTbUdRZmlURm9BQVNDajNhSEZqQzZLdE8yU0o4M1l3aEZyVGRjU2pHV1ZhSWN4VnV5Q3Q2
VDF5bFJxU1lyWnRkZUN4dE5sZjFXRUFMa0dJR3dBQUFBQUFFQUFBQUFBQUFBQUFBQUFBQUFBQUFB
QUFBQUFBQUFBQUFBQUFBQUFBQQ==
```

### Verificación
- El sistema valida que la firma corresponde al texto
- Confirma que fue generada por el responsable autorizado
- Verifica que no ha sido modificada

## 🔧 Configuración Avanzada

### Variables de Configuración
```php
// En firma_digital_real.php
private $directorio_certificados = 'certificados/';
private $directorio_firmas = 'firmas_digitales/';
```

### Personalización de Texto
```php
// Modificar generarTextoInsignia() para personalizar el formato
public function generarTextoInsignia($datos_insignia) {
    $texto = "Certificado de Insignia Digital - TecNM\n";
    $texto .= "Alumno: " . $datos_insignia['destinatario'] . "\n";
    // ... más campos
    return $texto;
}
```

## 🧪 Pruebas del Sistema

### Ejecutar Pruebas Completas
1. Ir a `prueba_firma_digital_completa.php`
2. Hacer clic en "Ejecutar Prueba Completa del Sistema"
3. Revisar todos los componentes generados

### Componentes de Prueba
- ✅ Ejemplo de firma digital
- ✅ Texto de insignia generado
- ✅ Firma digital simulada
- ✅ Insignia con firma integrada
- ✅ Datos de verificación

## 🔒 Consideraciones de Seguridad

### Certificados
- **Almacenamiento seguro**: Los certificados .key deben estar protegidos
- **Permisos restrictivos**: Solo el servidor web debe acceder
- **Respaldo seguro**: Mantener copias de seguridad encriptadas

### Contraseñas
- **Complejidad**: Usar contraseñas fuertes
- **Rotación**: Cambiar contraseñas periódicamente
- **Almacenamiento**: No almacenar en texto plano

### Verificación
- **Validación**: Siempre verificar firmas antes de confiar
- **Certificados**: Usar solo certificados válidos y no expirados
- **Auditoría**: Mantener logs de todas las operaciones

## 🆘 Solución de Problemas

### Error: "Certificado .cer no encontrado"
- Verificar que el archivo existe en `certificados/`
- Comprobar permisos de lectura
- Validar la ruta en el formulario

### Error: "No se pudo leer la clave privada"
- Verificar permisos del archivo .key
- Comprobar que la contraseña es correcta
- Validar formato del archivo

### Error: "Firma digital inválida"
- Verificar que el texto original es exacto
- Comprobar que la firma Base64 es completa
- Validar que el certificado corresponde

## 📞 Soporte

Para soporte técnico o consultas sobre el sistema de firma digital:

- **Documentación**: Revisar este README
- **Pruebas**: Usar `prueba_firma_digital_completa.php`
- **Verificación**: Usar `verificar_firma_digital_real.php`

## 🔄 Actualizaciones

### Versión 1.0
- ✅ Sistema básico de firma digital
- ✅ Integración con responsable_emision
- ✅ Verificación pública
- ✅ Interfaz de administración

### Próximas Versiones
- 🔄 Soporte para múltiples certificados
- 🔄 Firma de documentos PDF
- 🔄 Integración con blockchain
- 🔄 API REST para verificación

---

**Sistema de Firma Digital Real TecNM v1.0**  
*Implementación completa de firma digital para Insignias Digitales*
