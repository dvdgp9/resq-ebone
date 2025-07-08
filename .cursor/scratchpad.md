# ResQ - Sistema de Gestión de Socorristas

## Background and Motivation

**Proyecto**: ResQ - Sistema web PHP para gestión de socorristas en instalaciones acuáticas
**URL**: https://resq.ebone.es  
**Objetivo**: Sistema completo con funcionalidades para socorristas y administradores

### 🎯 **NUEVA SOLICITUD: SISTEMA DE PERMISOS UNIFICADO** (Enero 2025)

**PROBLEMA IDENTIFICADO:**
- Duplicación de datos entre tablas `coordinadores` y `admins`
- Tabla intermedia `admin_coordinadores` vacía (0 registros)
- Gestión manual de administradores (superadmin debe ir a BD)
- Sistema de permisos no implementado

**SOLUCIÓN PROPUESTA:**
- **Unificar** `coordinadores` + `admins` en una sola tabla
- **Implementar** sistema de permisos por tipo de usuario
- **Crear** interfaz web para gestión de administradores

**TIPOS DE USUARIOS:**
1. **Superadmin**: Ve todo y a todos
2. **Admin**: Tiene coordinadores asignados y puede ver sus instalaciones/socorristas
3. **Coordinador**: Tiene instalaciones asignadas y puede ver sus socorristas

---

## Project Status Board

### 🎯 **FASE 1: MIGRACIÓN DE DATOS** - ✅ **COMPLETADO**
- [x] **1A**: Migrar coordinador actual a `admins` con `tipo='coordinador'` 
- [x] **1B**: Actualizar FK en `instalaciones` para apuntar a nueva ID
- [x] **1C**: Verificar que todo funciona igual que antes

### 🎯 **FASE 2: GESTIÓN DE ADMINISTRADORES** - ✅ **COMPLETADO**
- [x] **2A**: Crear controlador `admin/administradores.php`
- [x] **2B**: Crear vista `admin/administradores.php`
- [x] **2C**: Agregar card "Gestión Admin" en dashboard

### 🎯 **FASE 3: SISTEMA DE PERMISOS** - ✅ **COMPLETADO**
- [x] **3A**: Implementar permisos en instalaciones
- [x] **3B**: Implementar permisos en socorristas
- [x] **3C**: Implementar permisos en coordinadores

### 🎯 **FASE 4: TESTING Y LIMPIEZA** - ⏳ **PENDIENTE**
- [ ] **4A**: Testing completo del sistema
- [ ] **4B**: Eliminar tabla `coordinadores` antigua
- [ ] **4C**: Limpieza y optimización final

## Current Status / Progress Tracking

**🚀 ESTADO ACTUAL: EJECUTOR - FASE 3 COMPLETADA**

### ✅ **COMPLETADO: SISTEMA DE PERMISOS UNIFICADO**

**📅 Fecha:** 2025-01-12  
**🎯 Fase:** 3 de 4 - Sistema de Permisos

**FUNCIONALIDADES IMPLEMENTADAS:**

### **🔐 Sistema de Permisos por Tipo de Usuario:**
- **🔑 SUPERADMIN**: Ve todo, gestiona todo (instalaciones, coordinadores, socorristas, administradores)
- **👨‍💼 ADMIN**: Ve coordinadores asignados + sus instalaciones/socorristas (solo lectura)
- **👥 COORDINADOR**: Ve solo sus instalaciones/socorristas (puede gestionar socorristas)

### **📊 Permisos Implementados:**
- **✅ Instalaciones**: Filtrado por tipo de usuario, JOIN con tabla admins
- **✅ Coordinadores**: Solo superadmins pueden gestionar, otros solo ven según asignación
- **✅ Socorristas**: Coordinadores pueden gestionar sus socorristas, admins solo lectura
- **✅ Administradores**: Solo superadmins pueden gestionar (ya implementado en Fase 2)

**ARCHIVOS MODIFICADOS:**
- `classes/AdminService.php` - Métodos getInstalaciones(), getCoordinadores(), getSocorristas() con permisos
- `controllers/admin/instalaciones.php` - Usa nuevo sistema de permisos
- `controllers/admin/coordinadores.php` - Verificaciones de permisos para CRUD
- `controllers/admin/socorristas.php` - Verificaciones de permisos para CRUD
- `classes/AdminAuthService.php` - **ARREGLADO** - Query y sesiones actualizadas para nueva estructura

**PROBLEMA RESUELTO:**
- ✅ **AdminAuthService arreglado** - Query no hacía JOIN con tabla coordinadores antigua
- ✅ **Variables de sesión actualizadas** - Se usa telefono en lugar de coordinador_id
- ✅ **Método esSuperAdmin() simplificado** - Ya no depende del coordinador_id obsoleto
- ✅ **URLs JavaScript corregidas** - Usaba /controllers/ en lugar de /admin/api/
- ✅ **Conexión BD añadida** - Faltaba $db = Database::getInstance()->getConnection()
- ✅ **Archivos debug eliminados** - Limpieza completa del sistema
- ✅ **Sistema de permisos completo** - Todos los controladores y servicios actualizados

**PRÓXIMO PASO:** Testing completo y limpieza final (Fase 4)

---

### 🚨 **ISSUES IDENTIFICADOS Y RESUELTOS:**

### **Issue 1: Botiquín - Coordinador no ve instalaciones** ✅ **RESUELTO**
**Causa:** `AdminPermissionsService` ya estaba actualizado correctamente
**Solución:** Verificado que usa tabla `admins` correctamente

### **Issue 2: Login de Socorristas - DNI no funciona** ✅ **RESUELTO**
**Causa:** `AuthService.php` línea 25 - JOIN con tabla `coordinadores` obsoleta
**Solución:** Actualizado JOIN a tabla `admins` + filtro `c.tipo = 'coordinador'`
**Archivo:** `classes/AuthService.php`
**Cambio:** `JOIN coordinadores c` → `JOIN admins c ON i.coordinador_id = c.id WHERE ... AND c.tipo = 'coordinador'`

---

### 🎯 **READY TO START: FASE 1 - MIGRACIÓN DE DATOS**

**Con la documentación de BD completa y el plan definido, el proyecto está listo para comenzar la implementación del sistema de permisos unificado.**

## Key Challenges and Analysis

### **🔧 DETALLES TÉCNICOS CLAVE**

**Migración de datos:**
- Solo 1 coordinador actual en BD (David Guti)
- 4 instalaciones que deben mantener su coordinador_id
- Migración sencilla pero crítica para no perder acceso

**Riesgos identificados:**
- ⚠️ **Migración de datos**: Coordinador actual debe migrar sin perder acceso
- ⚠️ **FK updates**: Todas las referencias a coordinadores deben actualizarse
- ⚠️ **Sesiones**: Coordinador puede perder sesión activa

**Mitigaciones:**
- 🛡️ **Backup BD** antes de cada fase
- 🛡️ **Testing incremental** después de cada tarea
- 🛡️ **Rollback plan** si algo falla

### **✅ CRITERIOS DE ÉXITO GLOBAL**

1. **Superadmin** puede gestionar todos los admins desde panel web
2. **Admins** ven solo coordinadores asignados y sus datos
3. **Coordinadores** mantienen acceso a sus instalaciones
4. **Tabla única** `admins` reemplaza `coordinadores` + `admin_coordinadores`
5. **Sin regresiones** - todo funciona igual o mejor

---

## Executor's Feedback or Assistance Requests

### **🎯 PLANNER COMPLETADO - READY FOR EXECUTOR**

**📅 Fecha:** 2025-01-12

**PLAN CREADO:**
- ✅ **Análisis completo** de la propuesta de unificación
- ✅ **Plan de 4 fases** con tareas específicas y criterios de éxito
- ✅ **Identificación de riesgos** y mitigaciones
- ✅ **Estructura técnica** definida para nueva tabla unificada

**RECOMENDACIÓN:** Proceder con **Fase 1: Migración de Datos** - comenzar con migración del coordinador actual.

**READY FOR EXECUTOR MODE:** El plan está completo y listo para implementación.

## Lessons

### Lecciones Técnicas Clave
- **Arquitectura unificada**: Consolidar tablas similares mejora mantenibilidad
- **Migración incremental**: Fases pequeñas con testing reduce riesgos
- **Sistema de permisos**: Diseño simple pero efectivo es mejor que complejo
- **Documentación de BD**: Analizar estructura antes de cambios críticos
- **Backup y rollback**: Siempre tener plan de recuperación en migraciones
