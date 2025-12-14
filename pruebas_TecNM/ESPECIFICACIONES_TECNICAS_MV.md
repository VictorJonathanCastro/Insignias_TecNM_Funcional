# 🖥️ ESPECIFICACIONES TÉCNICAS DETALLADAS - MÁQUINA VIRTUAL INSIGNIAS TECNM

## 📋 **RESUMEN EJECUTIVO**

**Proyecto:** Sistema de Insignias Digitales TecNM  
**Solicitante:** [Su nombre y cargo]  
**Fecha:** [Fecha actual]  
**Prioridad:** Alta - Proyecto Estratégico Institucional  

---

## 🎯 **OBJETIVOS DEL SISTEMA**

### **Objetivo Principal:**
Implementar una plataforma digital integral para la gestión, otorgamiento y verificación de insignias académicas y profesionales en el Tecnológico Nacional de México.

### **Objetivos Específicos:**
1. **Digitalizar** el proceso de reconocimientos académicos
2. **Automatizar** la carga masiva de datos via Excel
3. **Implementar** sistema de verificación pública
4. **Facilitar** la portabilidad de credenciales
5. **Integrar** con sistemas existentes del TecNM

---

## 🏗️ **ARQUITECTURA DEL SISTEMA**

### **Componentes Principales:**

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Web Interface)                 │
├─────────────────────────────────────────────────────────────┤
│  • Panel Administrativo    • Verificación Pública          │
│  • Dashboard Estudiantil   • Carga Masiva Excel            │
│  • Gestión de Insignias    • Reportes y Estadísticas       │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (API REST)                      │
├─────────────────────────────────────────────────────────────┤
│  • Autenticación JWT       • Procesamiento de Excel         │
│  • Validación de Datos     • Generación de Certificados    │
│  • Gestión de Metadatos    • Integración Social            │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    BASE DE DATOS                           │
├─────────────────────────────────────────────────────────────┤
│  • MySQL 8.0               • 12 Tablas Principales        │
│  • Vista de Metadatos      • Índices Optimizados           │
│  • Procedimientos Almacenados • Triggers de Auditoría       │
└─────────────────────────────────────────────────────────────┘
```

---

## 💻 **ESPECIFICACIONES DE HARDWARE**

### **Configuración Mínima Recomendada:**

| Componente | Especificación | Justificación |
|------------|----------------|---------------|
| **CPU** | 4 vCPU (2.4 GHz) | Procesamiento de archivos Excel masivos |
| **RAM** | 16 GB DDR4 | Cache de base de datos y sesiones |
| **Almacenamiento** | 200 GB SSD | Sistema + datos + logs + backups |
| **Red** | 1 Gbps | Transferencia de archivos y API |
| **Backup** | 500 GB adicional | Respaldo diario con retención |

### **Configuración Óptima para Producción:**

| Componente | Especificación | Justificación |
|------------|----------------|---------------|
| **CPU** | 8 vCPU (3.0 GHz) | Manejo de 1000+ usuarios concurrentes |
| **RAM** | 32 GB DDR4 | Cache completo de BD + múltiples procesos |
| **Almacenamiento** | 500 GB SSD NVMe | Alto rendimiento para I/O intensivo |
| **Red** | 10 Gbps | Distribución de imágenes y API |
| **Backup** | 1 TB adicional | Retención extendida + réplicas |

---

## 🔧 **ESPECIFICACIONES DE SOFTWARE**

### **Sistema Operativo:**
```bash
# Ubuntu Server 20.04 LTS (Recomendado)
- Kernel: 5.4+
- Arquitectura: x86_64
- Actualizaciones: Automáticas de seguridad
- Soporte: Hasta 2025
```

### **Stack Tecnológico:**

#### **Servidor Web:**
```bash
# Apache 2.4+ (Recomendado)
- Módulos: mod_ssl, mod_rewrite, mod_headers
- Configuración: MPM prefork para PHP
- SSL: TLS 1.3 con certificado válido
- Compresión: gzip para archivos estáticos
```

#### **Base de Datos:**
```sql
-- MySQL 8.0+ (Recomendado)
- Engine: InnoDB con configuración optimizada
- Charset: utf8mb4 para soporte Unicode completo
- Buffer Pool: 70% de RAM disponible
- Log Files: 256MB cada uno
- Connections: 200 máximo
```

#### **PHP:**
```ini
# PHP 8.1+ con extensiones críticas
extension=mysqli      # Conexión a MySQL
extension=gd          # Procesamiento de imágenes
extension=curl        # APIs externas
extension=zip         # Archivos Excel
extension=json        # APIs REST
extension=mbstring    # Caracteres especiales
extension=openssl     # Seguridad
extension=redis       # Cache de sesiones
```

#### **Herramientas de Desarrollo:**
```bash
# Composer 2.0+
- Gestión de dependencias PHP
- Autoloader optimizado
- Cache de clases

# PhpSpreadsheet 1.29+
- Lectura/escritura de Excel
- Soporte para .xlsx y .xls
- Validación de datos

# Git 2.30+
- Control de versiones
- Integración continua
```

---

## 📊 **CAPACIDADES DE PROCESAMIENTO**

### **Carga Masiva Excel:**
- **Archivos:** Hasta 10 MB por archivo
- **Registros:** 50,000+ filas por procesamiento
- **Tiempo:** < 5 minutos para 10,000 registros
- **Validación:** Automática con reporte de errores
- **Formatos:** .xlsx, .xls compatibles

### **Usuarios Concurrentes:**
- **Desarrollo:** 50 usuarios simultáneos
- **Pruebas:** 200 usuarios simultáneos  
- **Producción:** 1,000+ usuarios simultáneos
- **Pico:** 2,000 usuarios (períodos de alta demanda)

### **Volumen de Datos:**
- **Insignias anuales:** 1,000,000+ registros
- **Metadatos:** 50+ campos por insignia
- **Imágenes:** 2 TB+ de almacenamiento
- **Logs:** 100 GB+ mensuales
- **Backups:** 500 GB+ con retención de 30 días

---

## 🔒 **CONFIGURACIÓN DE SEGURIDAD**

### **Firewall:**
```bash
# Puertos abiertos
22    # SSH (acceso restringido por IP)
80    # HTTP (redirección a HTTPS)
443   # HTTPS (tráfico principal)
3306  # MySQL (acceso interno únicamente)

# Puertos bloqueados
21    # FTP
23    # Telnet
25    # SMTP
53    # DNS
```

### **SSL/TLS:**
```bash
# Certificado SSL
- Tipo: Wildcard (*.tecnm.mx) o específico
- Algoritmo: RSA 2048+ o ECDSA
- Protocolo: TLS 1.3 mínimo
- Renovación: Automática con Certbot
```

### **Autenticación:**
```bash
# Métodos de acceso
- SSH: Claves públicas únicamente
- Web: Autenticación JWT + LDAP
- API: Tokens de acceso con expiración
- Base de datos: Usuarios específicos por aplicación
```

---

## 🌐 **CONFIGURACIÓN DE RED**

### **Conectividad:**
- **Ancho de banda:** Mínimo 100 Mbps simétrico
- **Latencia:** < 50ms para usuarios nacionales
- **Uptime:** 99.9% de disponibilidad garantizada
- **DNS:** Subdominio dedicado (insignias.tecnm.mx)

### **CDN (Opcional):**
- **Distribución:** Imágenes de insignias
- **Cache:** Archivos estáticos (CSS, JS, imágenes)
- **Geolocalización:** Servidores en México
- **Compresión:** Automática para contenido web

---

## 📈 **MONITOREO Y MANTENIMIENTO**

### **Sistema de Monitoreo:**
```bash
# Métricas críticas
- CPU: < 80% uso promedio
- RAM: < 85% uso promedio  
- Disco: < 90% uso promedio
- Red: < 80% ancho de banda
- BD: < 100 conexiones simultáneas
```

### **Alertas Automáticas:**
- **Críticas:** < 5 minutos respuesta
- **Importantes:** < 30 minutos respuesta
- **Informativas:** < 2 horas respuesta
- **Canales:** Email + SMS + Slack

### **Mantenimiento Programado:**
- **Horario:** Domingos 2:00 - 6:00 AM
- **Frecuencia:** Mensual
- **Notificación:** 48 horas de anticipación
- **Duración:** Máximo 4 horas

---

## 🔄 **BACKUP Y RECUPERACIÓN**

### **Estrategia de Backup:**
```bash
# Backup completo diario
- Base de datos: Dump completo + incremental
- Archivos: rsync con compresión
- Configuración: Git repository
- Retención: 30 días locales + 90 días remotos
```

### **Recuperación:**
- **RTO:** 4 horas máximo
- **RPO:** 24 horas máximo
- **Pruebas:** Mensuales de recuperación
- **Documentación:** Procedimientos detallados

---

## 📋 **REQUERIMIENTOS DE ACCESO**

### **Permisos Administrativos:**
- **SSH Root:** Configuración inicial únicamente
- **Sudo:** Para instalación de paquetes
- **MySQL:** Usuario específico para la aplicación
- **Apache:** Usuario www-data con permisos limitados

### **Usuarios del Sistema:**
```bash
# Usuario principal de la aplicación
- Nombre: insignias_app
- Grupo: www-data
- Shell: /bin/false
- Home: /var/www/insignias

# Usuario de base de datos
- Nombre: insignias_db
- Permisos: SELECT, INSERT, UPDATE, DELETE
- Host: localhost únicamente
```

---

## 🚀 **PLAN DE IMPLEMENTACIÓN**

### **Fase 1: Configuración Inicial (Semana 1)**
- [ ] Provisión de máquina virtual
- [ ] Instalación de sistema operativo
- [ ] Configuración de red y firewall
- [ ] Instalación de software base

### **Fase 2: Desarrollo (Semanas 2-4)**
- [ ] Configuración de entorno de desarrollo
- [ ] Instalación de dependencias PHP
- [ ] Configuración de base de datos
- [ ] Despliegue de aplicación

### **Fase 3: Pruebas (Semanas 5-6)**
- [ ] Pruebas de carga masiva
- [ ] Pruebas de rendimiento
- [ ] Pruebas de seguridad
- [ ] Optimización de configuración

### **Fase 4: Producción (Semanas 7-8)**
- [ ] Migración de datos
- [ ] Configuración de monitoreo
- [ ] Capacitación de usuarios
- [ ] Go-live del sistema

---

## 💰 **ANÁLISIS DE COSTOS**

### **Costos de Infraestructura:**
- **Máquina Virtual:** $X,XXX MXN/mes
- **Almacenamiento:** $XXX MXN/mes
- **Ancho de banda:** $XXX MXN/mes
- **Backup:** $XXX MXN/mes
- **Monitoreo:** $XXX MXN/mes

### **ROI Estimado:**
- **Ahorro anual:** $2,000,000+ MXN
- **Eficiencia:** 80% reducción en procesos manuales
- **Retorno:** 300% en el primer año

---

## 📞 **CONTACTO Y SOPORTE**

### **Equipo del Proyecto:**
- **Líder Técnico:** [Nombre] - [Email] - [Teléfono]
- **Desarrollador:** [Nombre] - [Email] - [Teléfono]
- **DBA:** [Nombre] - [Email] - [Teléfono]

### **Soporte Técnico:**
- **Nivel 1:** Soporte básico (8:00 - 18:00)
- **Nivel 2:** Soporte avanzado (24/7)
- **Nivel 3:** Soporte crítico (< 1 hora respuesta)

---

## 📎 **ANEXOS TÉCNICOS**

1. **Scripts de Instalación** (`instalar.sh`)
2. **Configuración de Base de Datos** (`BD/estructura_completa_con_metadatos.sql`)
3. **Script de Verificación** (`verificar_sistema.php`)
4. **Documentación de API** (`docs/api_documentation.md`)
5. **Manual de Usuario** (`docs/manual_usuario.pdf`)

---

**¡Excelente tarde equipo! 🎓**

*Especificaciones técnicas desarrolladas con ❤️ para el TecNM*
