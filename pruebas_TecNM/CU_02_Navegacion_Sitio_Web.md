# 📋 CU-02: Navegación por el Sitio Web del Sistema de Insignias Digitales TecNM

| Campo | Descripción |
|-------|-------------|
| **Versión** | Versión 1.0 |
| **Autores** | Usuario |
| **Objetivos Asociados** | Permitir al Usuario explorar y acceder a diversas secciones y contenido del Sistema de Insignias Digitales TecNM según sus necesidades e intereses. |
| **Requisitos Asociados** | El Sitio Web debe tener una interfaz intuitiva y fácil de navegar. |
| **Descripción** | El Usuario navega por el Sitio Web para acceder a diversas secciones y contenido del sistema de insignias digitales. |
| **Precondición** | El Sitio Web debe estar disponible y accesible. |
| **Postcondición** | El Usuario ha explorado las diferentes secciones del Sitio Web según sus necesidades e intereses. |
| **Importancia** | Vital. |
| **Urgencia** | Inmediatamente. |

---

## Secuencia Normal

| Paso | Acción |
|------|--------|
| 1 | El Usuario accede al Sitio Web del Sistema de Insignias Digitales TecNM. |
| 2 | El Usuario navega a través de las diferentes secciones del Sitio Web. |

### Secciones Disponibles para Navegación:

| Opción | Descripción |
|--------|-------------|
| 2.1 | **Visitar inicio**: Acceder a la página principal del sistema que muestra información general, características del sistema, y acceso rápido a funcionalidades principales. |
| 2.2 | **Verificación pública de insignias**: Consultar y verificar la autenticidad de cualquier insignia mediante código único de identificación, sin necesidad de autenticación. |
| 2.3 | **Consulta de insignias**: Buscar y consultar información pública de insignias otorgadas mediante diferentes criterios de búsqueda (código, estudiante, categoría, periodo). |
| 2.4 | **Dashboard estudiantil**: Acceder al panel personalizado para estudiantes autenticados, donde pueden visualizar sus insignias recibidas, historial completo, y descargar certificados digitales. |
| 2.5 | **Panel de administración**: Acceder al panel administrativo (solo para usuarios con rol de Administrador) para gestionar usuarios, insignias, carga masiva, metadatos, categorías, responsables, centros IT, periodos, reportes y configuración del sistema. |
| 2.6 | **Compartir insignias**: Acceder a las funcionalidades para compartir insignias verificadas en redes sociales (Facebook, Twitter, LinkedIn, WhatsApp) con imágenes compartibles generadas automáticamente. |
| 2.7 | **Información del sistema**: Consultar información sobre el Sistema de Insignias Digitales TecNM, estándares de credenciales digitales, metadatos requeridos, y documentación del sistema. |
| 2.8 | **Iniciar sesión**: Acceder al sistema de autenticación para usuarios (estudiantes y administradores) que requieren acceso a funcionalidades restringidas. |

---

## Excepciones

| Paso | Acción |
|------|--------|
| 2 | Si se intenta acceder a una página no encontrada, se muestra un mensaje de error 404 brindando la opción de regresar a la página anterior o a la página principal. |
| 2.4 | Si el usuario intenta acceder al Dashboard estudiantil sin estar autenticado, el sistema redirige automáticamente a la página de inicio de sesión. |
| 2.5 | Si el usuario intenta acceder al Panel de administración sin tener rol de Administrador, se muestra un mensaje indicando que no tiene permisos para acceder a esta sección. |
| 2.3 | Si no se encuentran resultados en la búsqueda de insignias, se muestra un mensaje informativo indicando que no se encontraron insignias con los criterios especificados. |

---

## Comentarios

Este caso de uso refleja el proceso general de navegación por el Sitio Web del Sistema de Insignias Digitales TecNM, permitiendo al Usuario explorar y acceder a contenido relevante según sus intereses y necesidades.

La navegación debe ser intuitiva y accesible, con menús claramente organizados y opciones de búsqueda y filtrado eficientes. Se recomienda implementar un diseño responsive que permita una experiencia óptima en diferentes dispositivos (computadoras, tablets, smartphones).

El sistema debe garantizar que las secciones públicas (verificación, consulta) sean accesibles sin autenticación, mientras que las secciones privadas (dashboard estudiantil, panel administrativo) requieran la autenticación correspondiente con los permisos adecuados.

Es importante que la navegación sea fluida y que los usuarios puedan encontrar rápidamente la información que buscan, especialmente la funcionalidad de verificación pública de insignias, que es una de las características principales del sistema.

---

**Última actualización:** [Fecha actual]  
**Proyecto:** Sistema de Insignias Digitales TecNM  
**Versión:** 1.0

