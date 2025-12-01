# 📋 1.3. Alcances y Limitaciones

A continuación, se detallan los alcances y limitaciones del proyecto del **Sistema de Insignias Digitales TecNM**, destacando las funcionalidades específicas que se implementarán y las restricciones que se deben considerar.

---

## 1.3.1. Alcances

### • Gestión de Usuarios
✓ Desde el panel de administrador se puede agregar, editar y gestionar usuarios del sistema.  
✓ Se pueden asignar diferentes roles (Administrador, Usuario estándar) con permisos diferenciados.  
✓ El sistema permite autenticación mediante sesiones seguras con validación de credenciales.

### • Gestión de Insignias
✓ Desde el panel de administrador se puede crear, editar, actualizar y eliminar insignias digitales.  
✓ Se puede gestionar la información completa de las insignias incluyendo: nombre, descripción, categoría, tipo, criterios de emisión, y archivos visuales.  
✓ El sitio Web permite visualizar las insignias disponibles en la sección correspondiente y realizar búsquedas por diferentes criterios.

### • Gestión de Metadatos
✓ Se puede agregar, editar y actualizar todos los metadatos requeridos para cada insignia otorgada, incluyendo:
  - Código de identificación único
  - Información del destinatario (estudiante)
  - Descripción y criterios de emisión
  - Fecha de otorgamiento y autorización
  - Responsable de la emisión
  - Evidencias y archivos relacionados
✓ El sitio Web permite visualizar los metadatos completos en la sección de verificación pública.

### • Carga Masiva de Datos via Excel
✓ Se puede realizar carga masiva de datos desde archivos Excel para los siguientes tipos:
  - Insignias otorgadas
  - Destinatarios (estudiantes)
  - Centros IT (Institutos Tecnológicos)
  - Tipos de insignia
  - Categorías de insignia
  - Periodos de emisión
✓ El sistema genera plantillas Excel descargables para facilitar la carga de datos.  
✓ Se proporciona un reporte detallado de éxitos y errores después de cada carga masiva.  
✓ Se mantiene un historial completo de todas las cargas masivas realizadas.

### • Gestión de Categorías y Tipos de Insignia
✓ Se puede agregar, editar y actualizar las categorías de insignias disponibles en el sistema.  
✓ Se puede gestionar los diferentes tipos de insignias y sus características específicas.  
✓ El sitio Web permite visualizar las categorías y tipos en las secciones correspondientes.

### • Gestión de Responsables de Emisión
✓ Se puede agregar, editar y actualizar la información de los responsables que otorgan insignias.  
✓ Se puede asociar certificados digitales y firmas digitales a cada responsable.  
✓ El sistema permite gestionar la información de contacto y códigos de identificación de responsables.

### • Gestión de Centros IT
✓ Se puede agregar, editar y actualizar la información de los Institutos Tecnológicos del TecNM.  
✓ Se puede asociar insignias a centros específicos.  
✓ El sitio Web permite visualizar la información de los centros en las secciones correspondientes.

### • Sistema de Verificación Pública
✓ El sitio Web permite la verificación pública de cualquier insignia mediante código único de identificación.  
✓ Se puede consultar la autenticidad y validez de las insignias sin necesidad de autenticación.  
✓ El sistema muestra información completa y verificable de cada insignia otorgada.  
✓ El sitio Web incluye un botón "CONSULTAR INSIGNIA" con diseño moderno y profesional que permite acceso directo a la consulta pública de insignias desde la página de inicio de sesión y otras secciones del sistema.

### • Dashboard Estudiantil
✓ Los estudiantes pueden acceder a un dashboard personalizado para visualizar sus insignias recibidas.  
✓ Se puede consultar el historial completo de insignias otorgadas.  
✓ Los estudiantes pueden descargar certificados digitales de sus insignias.

### • Compartir Insignias en Redes Sociales
✓ El sistema permite compartir insignias en redes sociales (Facebook, Twitter, LinkedIn, WhatsApp).  
✓ Se genera automáticamente una imagen compartible con la información de la insignia.  
✓ El sitio Web incluye botones de compartir en cada insignia verificada.

### • Gestión de Periodos de Emisión
✓ Se puede agregar, editar y actualizar los periodos escolares disponibles en el sistema.  
✓ Se puede asociar insignias a periodos específicos para organización y reportes.  
✓ El sitio Web permite filtrar insignias por periodo de emisión.

### • Sistema de Firma Digital
✓ Se puede gestionar certificados digitales (.cer) y claves privadas (.key) para firmas digitales.  
✓ El sistema genera firmas digitales automáticamente al crear insignias.  
✓ Se puede verificar la autenticidad de las firmas digitales en la verificación pública.

### • Historial y Auditoría
✓ El sistema mantiene un historial completo de todas las cargas masivas realizadas.  
✓ Se registran todas las actividades importantes del sistema para auditoría.  
✓ Se pueden consultar logs de errores y accesos al sistema.

### • Reportes y Estadísticas
✓ El panel de administrador permite generar reportes de insignias otorgadas.  
✓ Se pueden consultar estadísticas de uso del sistema.  
✓ El sistema proporciona información sobre el estado de las insignias (aprobadas, pendientes, etc.).

---

## 1.3.2. Limitaciones

### • Gestión de Usuarios
✓ Solo los usuarios con rol de Administrador pueden agregar, editar y eliminar otros usuarios del sistema.  
✓ Los usuarios estándar no tienen acceso al panel de administración.  
✓ Se recomienda que solo usuarios autorizados tengan permisos de administrador para mantener la seguridad del sistema.

### • Carga Masiva de Datos via Excel
✓ Los archivos Excel deben tener un tamaño máximo de 10 MB por archivo.  
✓ Solo se aceptan formatos de archivo Excel (.xlsx o .xls).  
✓ Es necesario que los archivos Excel sigan el formato de la plantilla proporcionada por el sistema.  
✓ Los datos deben estar completos y correctamente formateados según las especificaciones de cada tipo de carga.  
✓ El sistema procesa los archivos de forma secuencial, por lo que archivos muy grandes pueden tardar varios minutos en procesarse.

### • Gestión de Insignias
✓ Para otorgar una insignia, primero debe existir el tipo de insignia correspondiente en la base de datos.  
✓ Para asociar una insignia a un estudiante, primero debe existir el destinatario en la base de datos.  
✓ Solo se puede otorgar una insignia por estudiante según la política de duplicados del sistema (dependiendo de la configuración).  
✓ Es necesario completar todos los campos obligatorios de metadatos antes de otorgar una insignia.

### • Gestión de Metadatos
✓ Para editar los metadatos de una insignia, primero se debe seleccionar la insignia específica.  
✓ Algunos campos de metadatos no pueden ser modificados después de la aprobación de la insignia (dependiendo de la configuración).  
✓ Es necesario tener permisos de administrador para modificar metadatos de insignias ya otorgadas.

### • Gestión de Archivos e Imágenes
✓ Las imágenes de insignias deben estar en formatos compatibles (JPG, PNG, GIF).  
✓ El tamaño de las imágenes está limitado por la configuración del servidor (upload_max_filesize en PHP).  
✓ Solo se puede subir una imagen por insignia en el proceso de creación.

### • Sistema de Verificación Pública
✓ La verificación pública requiere el código único de identificación de la insignia.  
✓ Sin el código correcto, no se puede acceder a la información completa de la insignia.  
✓ La información mostrada en la verificación pública es de solo lectura.

### • Compartir en Redes Sociales
✓ Para compartir una insignia en redes sociales, primero debe estar verificada y aprobada.  
✓ La generación de imágenes compartibles requiere que la insignia tenga una imagen asociada.  
✓ El sistema depende de la disponibilidad de las APIs de las redes sociales para el compartir.

### • Requisitos Técnicos
✓ El sistema requiere que estén instaladas las dependencias de Composer (PhpSpreadsheet para Excel).  
✓ Es necesario tener configurada correctamente la conexión a la base de datos MySQL.  
✓ El servidor debe tener PHP 7.4 o superior con las extensiones necesarias (mysqli, gd, curl, zip).  
✓ Se requiere acceso de administrador al servidor para configurar permisos de directorios (uploads/, logs/).

### • Gestión de Responsables
✓ Para asociar una firma digital a un responsable, primero debe existir el responsable en la base de datos.  
✓ Los certificados digitales (.cer) y claves privadas (.key) deben estar en formatos válidos.  
✓ Solo se puede asociar un certificado digital por responsable.

### • Gestión de Categorías
✓ Para crear una insignia de una categoría específica, primero debe existir la categoría en la base de datos.  
✓ No se pueden eliminar categorías que tengan insignias asociadas (dependiendo de la configuración de integridad referencial).

### • Limitaciones de Rendimiento
✓ El sistema está optimizado para procesar hasta 10,000 registros en menos de 5 minutos en condiciones normales.  
✓ Archivos Excel muy grandes (cercanos al límite de 10 MB) pueden requerir más tiempo de procesamiento.  
✓ El número de usuarios concurrentes está limitado por la capacidad del servidor (recomendado: 1,000+ usuarios simultáneos en producción).

### • Limitaciones de Seguridad
✓ El sistema requiere conexión HTTPS en producción para proteger datos sensibles.  
✓ Las contraseñas deben cumplir con políticas de seguridad establecidas.  
✓ El acceso a funciones administrativas está restringido a usuarios autenticados con rol de administrador.

### • Limitaciones de Almacenamiento
✓ El almacenamiento de imágenes está limitado por la capacidad del servidor.  
✓ Se recomienda realizar limpieza periódica de archivos temporales y logs antiguos.  
✓ Los backups automáticos tienen una retención limitada (recomendado: 30 días).

---

**Nota:** Algunas limitaciones pueden ser ajustadas mediante configuración del servidor o modificaciones en el código, pero se recomienda mantener estas restricciones para garantizar la seguridad, integridad y rendimiento del sistema.

---

**Última actualización:** [Fecha actual]  
**Proyecto:** Sistema de Insignias Digitales TecNM  
**Versión:** 1.0

