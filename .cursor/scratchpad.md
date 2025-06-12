# ResQ - Aplicación para Socorristas
**Dominio de despliegue**: resq.ebone.es

## Background and Motivation
Continuando con la implementación del panel de administración. El sistema principal ResQ está 100% funcional en resq.ebone.es. Necesitamos completar el panel de administración para que el superadmin pueda gestionar coordinadores, instalaciones y socorristas.

## Key Challenges and Analysis
- Mantener consistencia con el diseño existente
- Centralizar estilos en CSS
- API REST completa para CRUD operations
- Seguridad y autenticación admin
- UX intuitiva con modales y feedback

## High-level Task Breakdown

### Fase 1: Planificación y Estructura Base ✅ COMPLETADA
- [x] **Tarea 1.1**: Definir estructura de base de datos
  - *Criterio de éxito*: Diagrama ER con arquitectura Coordinadores→Instalaciones→Socorristas y tablas de formularios ✅
- [x] **Tarea 1.2**: Crear wireframes de las interfaces principales
  - *Criterio de éxito*: Mockups de página inicio, formularios y panel admin ✅
- [x] **Tarea 1.3**: Definir flujo de usuarios y permisos
  - *Criterio de éxito*: Documento con roles y permisos claramente definidos ✅

### Fase 2: Configuración del Entorno ✅ COMPLETADA
- [x] **Tarea 2.1**: Configurar estructura de proyecto PHP
  - *Criterio de éxito*: Estructura de carpetas y archivos base creada ✅
- [x] **Tarea 2.2**: Configurar base de datos MySQL/MariaDB
  - *Criterio de éxito*: Base de datos creada con todas las tablas ✅
- [x] **Tarea 2.3**: Configurar sistema de emails (PHPMailer)
  - *Criterio de éxito*: Envío de email de prueba funcionando ✅

### Fase 3: Desarrollo del Backend ✅ COMPLETADA
- [x] **Tarea 3.1**: Implementar sistema de autenticación
  - *Criterio de éxito*: Login/logout con DNI funcionando ✅
- [x] **Tarea 3.2**: Crear API endpoints para formularios
  - *Criterio de éxito*: CRUD completo para los 3 formularios ✅
- [ ] **Tarea 3.3**: Implementar panel de administración
  - *Criterio de éxito*: Gestión de usuarios e instalaciones funcionando

### Fase 4: Desarrollo del Frontend ✅ COMPLETADA
- [x] **Tarea 4.1**: Crear página de inicio con 3 botones
  - *Criterio de éxito*: Interfaz responsive y funcional ✅
- [x] **Tarea 4.2**: Desarrollar los 3 formularios (tipo Google Forms):
  - Control flujo de personas usuarias ✅
  - Incidencias ✅
  - Parte de accidente ✅
  - *Criterio de éxito*: Formularios validados, intuitivos y enviando datos correctamente ✅
- [ ] **Tarea 4.3**: Crear interfaz del panel de administración
  - *Criterio de éxito*: Panel completo y funcional

### Fase 5: Integración y Testing ✅ COMPLETADA
- [x] **Tarea 5.1**: Integrar sistema de notificaciones
  - *Criterio de éxito*: Emails enviándose correctamente tras completar formularios ✅
- [x] **Tarea 5.2**: Testing completo del sistema
  - *Criterio de éxito*: Todos los flujos probados y funcionando ✅
- [x] **Tarea 5.3**: Documentación y deployment
  - *Criterio de éxito*: Sistema desplegado y documentado ✅

## Project Status Board

### 🔄 En Progreso
- **Tarea 3.3**: Implementar panel de administración
- **Tarea 4.3**: Crear interfaz del panel de administración

### ✅ Completado
- ✅ **FASE 1 COMPLETA**: Planificación y Estructura Base
  - ✅ Tarea 1.1: Definir estructura de base de datos
  - ✅ Tarea 1.2: Crear wireframes de las interfaces principales  
  - ✅ Tarea 1.3: Definir flujo de usuarios y permisos
- ✅ **FASE 2 COMPLETA**: Configuración del Entorno
  - ✅ Tarea 2.1: Configurar estructura de proyecto PHP
  - ✅ Tarea 2.2: Configurar base de datos MySQL/MariaDB
  - ✅ Tarea 2.3: Configurar sistema de emails (PHPMailer)
- ✅ **FASE 3 COMPLETA**: Desarrollo del Backend
  - ✅ Tarea 3.1: Implementar sistema de autenticación
  - ✅ Tarea 3.2: Crear API endpoints para formularios
- ✅ **FASE 4 COMPLETA**: Desarrollo del Frontend
  - ✅ Tarea 4.1: Crear página de inicio con 3 botones (Dashboard)
  - ✅ Tarea 4.2: Desarrollar los 3 formularios (tipo Google Forms)
- ✅ **FASE 5 COMPLETA**: Integración y Testing
  - ✅ Tarea 5.1: Integrar sistema de notificaciones
  - ✅ Tarea 5.2: Testing completo del sistema
  - ✅ Tarea 5.3: Documentación y deployment
- ✅ **EXTRAS COMPLETADOS**:
  - ✅ Refactorización CSS: Centralizado en `assets/css/styles.css`
  - ✅ Sistema de autenticación completamente funcional
  - ✅ Dashboard con 3 botones principales funcionando

## Current Status / Progress Tracking

**Estado actual**: ✅ SISTEMA BASE COMPLETAMENTE FUNCIONAL
**Última actualización**: Codebase limpio, emails funcionando, formularios operativos
**Próximo paso**: Panel de administración (Tareas 3.3 y 4.3)

**🎯 HITOS ALCANZADOS**:
- ✅ **Infraestructura completa**: PHP + MySQL + Email configurados
- ✅ **Autenticación funcionando**: Login con DNI + Dashboard con 3 botones
- ✅ **CSS centralizado**: Mejor mantenibilidad y escalabilidad
- ✅ **Base de datos poblada**: Con datos de ejemplo para testing
- ✅ **API ENDPOINTS COMPLETOS**: Los 3 formularios con validación y notificaciones
- ✅ **VISTAS COMPLETAS**: Los 3 formularios funcionales con interfaz profesional

**📋 SISTEMA COMPLETAMENTE FUNCIONAL**:
- ✅ Control de Flujo: Vista + API + Email (/formulario/control-flujo)
- ✅ Incidencias: Vista + API + Email (/formulario/incidencias)
- ✅ Parte de Accidente: Vista + API + Email (/formulario/parte-accidente)

**📋 SIGUIENTE MILESTONE**: Panel de Administración
- Tarea 3.3: Backend del panel de administración (gestión de usuarios)
- Tarea 4.3: Frontend del panel de administración (interfaz web)
- Funcionalidades: CRUD de coordinadores, instalaciones y socorristas

## Executor's Feedback or Assistance Requests

### 🚀 MILESTONE ALCANZADO: CRUD COORDINADORES COMPLETO

**Estado:** ✅ **IMPLEMENTACIÓN COMPLETADA - LISTO PARA TESTING**

**Archivos Creados/Modificados:**
- `controllers/admin/coordinadores.php` - API REST completa (GET/POST/PUT/DELETE)
- `views/admin/coordinadores.php` - Vista con tabla, modales, JavaScript completo
- `assets/css/styles.css` - Estilos para tablas, modales, badges, responsive
- `index.php` - Rutas agregadas

**Funcionalidades Implementadas:**
- ✅ Listar coordinadores con estadísticas de instalaciones
- ✅ Crear nuevo coordinador (modal con validación)
- ✅ Editar coordinador existente 
- ✅ Desactivar coordinador (confirmación)
- ✅ Estados visual (activo/inactivo)
- ✅ Responsive design completo
- ✅ Integración con dashboard

**URLs Disponibles:**
- `/admin/coordinadores` - Vista de gestión
- `/admin/api/coordinadores` - API REST

**🔧 CORRECCIONES UX APLICADAS:**
✅ **Botón Cancelar**: Color gris visible con texto blanco
✅ **Errores en Modal**: Mensajes aparecen dentro del modal, no detrás
✅ **Limpieza**: Mensajes se limpian al abrir/cerrar modal
✅ **Borrado Físico**: Coordinadores sin instalaciones se eliminan completamente
✅ **Campo Activo Eliminado**: Simplificada la tabla y lógica
✅ **UX Mejorada**: Botón eliminar deshabilitado + tooltip para coordinadores con instalaciones
✅ **Vista Instalaciones**: Badge interactivo + tooltip + modal completo con detalles

**🔧 TESTING ACTUALIZADO:**
El usuario debe probar:
1. Acceder a `/admin/coordinadores` desde dashboard
2. Crear un coordinador nuevo
3. Probar email duplicado (error debe aparecer EN el modal)
4. Verificar botón cancelar es visible
5. Editar coordinador existente
6. **ELIMINAR coordinador** (sin instalaciones = borrado físico)
7. **Hover sobre botón eliminar deshabilitado** (coordinadores con instalaciones = tooltip explicativo)
8. **Hover sobre badge instalaciones** (tooltip con lista de instalaciones)
9. **Click en badge instalaciones** (modal completo con detalles y estadísticas)

**📁 ARCHIVO SQL CREADO:**
- `database/remove_activo_coordinadores.sql` - Para eliminar campo activo de BD

**Arquitectura mantenida:**
- Máxima simplicidad
- CSS centralizado (sin inline styles)
- Consistencia visual
- Código limpio y documentado

**🔮 MEJORAS FUTURAS IDENTIFICADAS:**
- [ ] **Modal Instalaciones**: Añadir botones editar/eliminar instalaciones desde modal de coordinadores
- [ ] **Gestión avanzada**: Reasignar instalaciones entre coordinadores
- [ ] **Filtros**: Búsqueda y filtrado en tabla de coordinadores

**¿Proceder con testing o implementar siguiente CRUD (Instalaciones)?**

## Lessons
- ✅ CSS styles centralizados en assets/css/styles.css
- ✅ APIs REST con proper HTTP status codes
- ✅ Modales con animaciones y UX moderna
- ✅ Validación tanto frontend como backend
- ✅ Responsive design desde el inicio
- ✅ **CRÍTICO**: Al eliminar campos de BD, actualizar TODAS las consultas SQL que los referencien

## 🎯 PLANNER: PLANIFICACIÓN DETALLADA PANEL DE ADMINISTRACIÓN

### 📋 ANÁLISIS DE ARQUITECTURA ACTUAL

**🗄️ TABLAS DE BASE DE DATOS EXISTENTES:**
- `coordinadores` (id, nombre, email, telefono, activo, timestamps)
- `instalaciones` (id, nombre, direccion, coordinador_id, activo, timestamps)  
- `socorristas` (id, dni, nombre, email, telefono, instalacion_id, activo, timestamps)
- `formularios` (id, socorrista_id, tipo_formulario, datos_json, fecha_creacion, notificacion_enviada)
- `sesiones` (id, socorrista_id, fecha_creacion, fecha_expiracion, activa)

**🔧 RELACIONES EXISTENTES:**
- coordinadores 1:N instalaciones
- instalaciones 1:N socorristas  
- socorristas 1:N formularios
- socorristas 1:N sesiones

**📁 CLASES EXISTENTES:**
- `AuthService` (autenticación de socorristas)
- `EmailService` (notificaciones PHPMailer)
- `SimpleEmailService` (fallback de email)

**🚀 CONTROLADORES EXISTENTES:**
- Naming pattern: `nombre_funcionalidad.php` (control_flujo.php, parte_accidente.php)
- Structure: API endpoints con JSON response
- Authentication: Verifican `$auth->estaAutenticado()`

**🎨 VISTAS EXISTENTES:**
- `views/login.php` - Login de socorristas
- `views/dashboard.php` - Dashboard de socorristas  
- `views/formularios/` - Formularios funcionales

### 🚀 REQUERIMIENTOS DEL PANEL ADMIN

**👥 USUARIOS DEL PANEL:**
- **Superadmin** (tú): Gestión completa del sistema
- **Coordinadores** (futuro): Gestión de sus instalaciones/socorristas asignadas

**🔐 SISTEMA DE AUTENTICACIÓN ADMIN:**
- **Separado del login de socorristas** (diferentes credenciales)
- Login único `/admin` para superadmin
- Tabla `admins` con email/password_hash
- Nueva tabla `admin_sesiones` para sesiones de administración

### 📋 TAREA 3.3: BACKEND DEL PANEL DE ADMINISTRACIÓN

**🗄️ NUEVAS TABLAS REQUERIDAS:**
```sql
-- Tabla de administradores (superadmin + futuros coordinadores)
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('superadmin', 'coordinador') DEFAULT 'coordinador',
    coordinador_id INT NULL, -- NULL para superadmin, FK para coordinadores
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinador_id) REFERENCES coordinadores(id) ON DELETE CASCADE
);

-- Tabla de sesiones admin (separada de sesiones de socorristas)
CREATE TABLE admin_sesiones (
    id VARCHAR(128) PRIMARY KEY,
    admin_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
```

**🔧 NUEVAS CLASES REQUERIDAS:**
1. **`AdminAuthService`** - Autenticación para admin panel
   - `login($email, $password)` - Login con email/password  
   - `estaAutenticadoAdmin()` - Verificación de sesión admin
   - `logout()` - Logout admin
   - `getAdminActual()` - Datos del admin logueado
   - `esSuperAdmin()` - Verificar si es superadmin

2. **`AdminService`** - Lógica de negocio del panel (CRUD simplificado)
   - `getCoordinadores()` - Listar coordinadores
   - `crearCoordinador($datos)` - Crear coordinador
   - `actualizarCoordinador($id, $datos)` - Actualizar coordinador
   - `getInstalaciones()` - Listar instalaciones
   - `crearInstalacion($datos)` - Crear instalación
   - `getSocorristas()` - Listar socorristas
   - `crearSocorrista($datos)` - Crear socorrista

**🚀 NUEVOS CONTROLADORES ADMIN:**
Naming pattern: `admin/nombre_funcionalidad.php`

1. **`controllers/admin/auth.php`** - Autenticación admin
   - POST `/admin/login` - Procesar login
   - POST `/admin/logout` - Cerrar sesión

2. **`controllers/admin/socorristas.php`** - CRUD Socorristas  
   - GET `/admin/api/socorristas` - Listar socorristas del coordinador
   - POST `/admin/api/socorristas` - Crear socorrista
   - PUT `/admin/api/socorristas/{id}` - Actualizar socorrista
   - DELETE `/admin/api/socorristas/{id}` - Desactivar socorrista

3. **`controllers/admin/instalaciones.php`** - CRUD Instalaciones
   - GET `/admin/api/instalaciones` - Listar instalaciones del coordinador
   - PUT `/admin/api/instalaciones/{id}` - Actualizar instalación

4. **`controllers/admin/reportes.php`** - Visualización de formularios
   - GET `/admin/api/reportes` - Formularios filtrados
   - GET `/admin/api/estadisticas` - Stats por instalación

**🔒 MIDDLEWARE DE SEGURIDAD:**
- Verificar que coordinador solo accede a SUS instalaciones/socorristas
- Validación de permisos por instalación
- Logs de acciones administrativas

### 📋 TAREA 4.3: FRONTEND DEL PANEL DE ADMINISTRACIÓN

**🎨 NUEVAS VISTAS ADMIN:**
Directory: `views/admin/`

1. **`views/admin/login.php`** - Login específico para coordinadores
   - Form: email + password
   - Estilo consistente con diseño actual
   - Separado completamente del login de socorristas

2. **`views/admin/dashboard.php`** - Dashboard principal del coordinador
   - Resumen de instalaciones bajo su gestión
   - Stats: Total socorristas, formularios recientes
   - Enlaces a secciones: Socorristas, Instalaciones, Reportes

3. **`views/admin/socorristas.php`** - Gestión de socorristas
   - Lista de socorristas por instalación
   - Modal para crear/editar socorrista
   - Campos: DNI, nombre, email, teléfono, instalación
   - Botones: Activar/Desactivar, Editar

4. **`views/admin/instalaciones.php`** - Gestión de instalaciones
   - Lista de instalaciones del coordinador
   - Edición de datos: nombre, dirección
   - Ver socorristas por instalación

5. **`views/admin/reportes.php`** - Visualización de formularios
   - Filtros: Instalación, Tipo formulario, Rango fechas
   - Tabla de formularios enviados
   - Detalles expandibles por formulario

**🎨 COMPONENTES UI REUTILIZABLES:**
- Modales para CRUD (create/edit)
- Tablas con paginación
- Filtros de búsqueda
- Cards de estadísticas
- Mensajes de éxito/error

### 🚦 RUTAS NUEVAS REQUERIDAS

**En `index.php` añadir:**
```php
// Rutas admin
case '/admin':
case '/admin/login':
    require_once 'views/admin/login.php';
    break;
    
case '/admin/dashboard':
    require_once 'views/admin/dashboard.php';
    break;
    
case '/admin/logout':
    require_once 'controllers/admin/auth.php';
    break;

// API Admin
case '/admin/api/socorristas':
    require_once 'controllers/admin/socorristas.php';
    break;
    
case '/admin/api/instalaciones':
    require_once 'controllers/admin/instalaciones.php';
    break;
    
case '/admin/api/reportes':
    require_once 'controllers/admin/reportes.php';
    break;
```

### 🔐 CONSIDERACIONES DE SEGURIDAD

**🛡️ SEPARACIÓN DE CONTEXTOS:**
- Sesiones admin completamente separadas de sesiones socorristas
- URLs diferentes (`/admin/*` vs rutas normales)
- Verificación de permisos por instalación

**🔒 CONTROL DE ACCESO:**
- Coordinador solo ve SUS instalaciones y socorristas
- Validación server-side de todos los permisos
- Logs de acciones administrativas

**🚨 VALIDACIONES:**
- DNI único al crear socorristas
- Email único de coordinadores
- Datos obligatorios por entidad

### 📋 PLAN DE IMPLEMENTACIÓN ESPECÍFICO

**✅ SUBTAREA 3.3.1: Preparación Base de Datos** - COMPLETADA
- ✅ Crear script `database/admin_tables.sql`
- ✅ Crear tabla `admins` (superadmin + futuros coordinadores)
- ✅ Crear tabla `admin_sesiones`
- ✅ Insertar usuario superadmin inicial
- ✅ Script PHP `database/install_admin.php` para instalación automática

**✅ SUBTAREA 3.3.2: Clases de Administración** - COMPLETADA
- ✅ Crear `classes/AdminAuthService.php` (login, logout, verificación sesiones)
- ✅ Crear `classes/AdminService.php` (CRUD coordinadores, instalaciones, socorristas)
- ✅ Mantener consistencia con naming actual

**🎯 SUBTAREA 3.3.3: APIs Administrativas**
- Crear `controllers/admin/auth.php`
- Crear `controllers/admin/socorristas.php`
- Crear `controllers/admin/instalaciones.php`
- Crear `controllers/admin/reportes.php`

**✅ SUBTAREA 4.3.1: Vistas Base Admin** - COMPLETADA
- ✅ Crear `views/admin/login.php` (email/password con diseño consistente)
- ✅ Crear `views/admin/dashboard.php` (estadísticas + navegación)
- ✅ Layout consistente con diseño actual
- ✅ Rutas integradas en `index.php`

**🎯 SUBTAREA 4.3.2: Interfaces CRUD**
- Crear `views/admin/socorristas.php`
- Crear `views/admin/instalaciones.php`
- JavaScript para modales y AJAX

**🎯 SUBTAREA 4.3.3: Sistema de Reportes**  
- Crear `views/admin/reportes.php`
- Filtros y visualización de formularios

### ✅ CRITERIOS DE ÉXITO

**🎯 TAREA 3.3 COMPLETADA CUANDO:**
- ✅ Coordinadores pueden loguearse con email/password
- ✅ APIs REST funcionando para CRUD de socorristas
- ✅ APIs REST funcionando para gestión de instalaciones  
- ✅ API de reportes filtrando correctamente
- ✅ Seguridad: Solo acceso a SUS instalaciones/socorristas
- ✅ Tests básicos de todos los endpoints

**🎯 TAREA 4.3 COMPLETADA CUANDO:**
- ✅ Login admin funcionando con diseño consistente
- ✅ Dashboard mostrando stats del coordinador
- ✅ CRUD de socorristas completamente funcional
- ✅ Gestión de instalaciones operativa
- ✅ Sistema de reportes filtrando y mostrando formularios
- ✅ Responsive design en todas las vistas admin
- ✅ Manejo de errores y feedback de usuario

### 🔧 PRÓXIMOS PASOS INMEDIATOS

1. **CONFIRMAR PLAN**: ¿Este enfoque está alineado con tus expectativas?
2. **CREDENCIALES ADMIN**: ¿Cómo quieres generar passwords iniciales para coordinadores?
3. **PERMISOS**: ¿Algún coordinador debería ver TODAS las instalaciones (super admin)?
4. **PRIORIDADES**: ¿Qué funcionalidad del admin es más urgente?

**Estado**: Plan detallado listo para implementación
**Bloqueadores**: Ninguno identificado  
**Estimación**: 2-3 sesiones de desarrollo (Backend + Frontend)