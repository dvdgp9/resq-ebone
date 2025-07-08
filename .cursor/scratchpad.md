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

### 🚀 **ESTADO ACTUAL: PLANNER - SISTEMA DE PERMISOS UNIFICADO**

**📅 ÚLTIMA ACTUALIZACIÓN:** 2025-01-12 
**🎯 OBJETIVO:** Unificar `coordinadores` + `admins` en una sola tabla con sistema de permisos

## High-level Task Breakdown

### **🎯 FASE 1: MIGRACIÓN DE DATOS**
**Objetivo:** Mover coordinador actual a tabla `admins`

- [ ] **Tarea 1A**: Migrar coordinador actual a `admins` con `tipo='coordinador'`
- [ ] **Tarea 1B**: Actualizar FK en `instalaciones` para apuntar a nueva ID
- [ ] **Tarea 1C**: Verificar que todo funciona igual que antes

**Criterio éxito:** Coordinador actual puede login y ve sus instalaciones

---

### **🎯 FASE 2: GESTIÓN DE ADMINISTRADORES**
**Objetivo:** Crear interfaz para que superadmin gestione admins

- [ ] **Tarea 2A**: Crear `controllers/admin/administradores.php` (CRUD básico)
- [ ] **Tarea 2B**: Crear `views/admin/administradores.php` (tabla + formularios)
- [ ] **Tarea 2C**: Añadir card "Gestión Admin" al dashboard
- [ ] **Tarea 2D**: Testing - crear/editar/eliminar administradores

**Criterio éxito:** Superadmin puede gestionar admins desde panel web

---

### **🎯 FASE 3: SISTEMA DE PERMISOS**
**Objetivo:** Implementar permisos por tipo de usuario

- [ ] **Tarea 3A**: Crear `AdminPermissionsService` (superadmin/admin/coordinador)
- [ ] **Tarea 3B**: Aplicar permisos a controladores existentes
- [ ] **Tarea 3C**: Actualizar vistas para mostrar solo datos permitidos
- [ ] **Tarea 3D**: Testing - verificar cada tipo de usuario

**Criterio éxito:** Cada tipo de usuario ve solo lo que debe ver

---

### **🎯 FASE 4: LIMPIEZA Y TESTING**
**Objetivo:** Eliminar código obsoleto y verificar sistema

- [ ] **Tarea 4A**: Eliminar tabla `coordinadores` (ya no se usa)
- [ ] **Tarea 4B**: Eliminar tabla `admin_coordinadores` (ya no se usa)
- [ ] **Tarea 4C**: Limpiar código que referencia tablas eliminadas
- [ ] **Tarea 4D**: Testing final completo

**Criterio éxito:** Sistema funciona sin tablas obsoletas

---

## 📊 **PROGRESO GLOBAL**

### **Estado Actual:**
- **Fase 1:** 0% - Pendiente migración de datos
- **Fase 2:** 0% - Pendiente gestión de administradores
- **Fase 3:** 0% - Pendiente sistema de permisos
- **Fase 4:** 0% - Pendiente limpieza final

### **Estructura Técnica Nueva:**
```sql
-- Tabla unificada propuesta
admins:
├── id (PK)
├── email (único)
├── password_hash 
├── nombre
├── telefono (nuevo para coordinadores)
├── tipo (ENUM: 'superadmin', 'admin', 'coordinador')
├── activo
├── fecha_creacion
└── fecha_actualizacion

-- Relación admin-coordinador (para admins que gestionan coordinadores)
admin_coordinadores:
├── admin_id (FK → admins.id WHERE tipo='admin')
└── coordinador_id (FK → admins.id WHERE tipo='coordinador')
```

### **Sistema de Permisos:**
- **superadmin**: Ve todo
- **admin**: Ve coordinadores asignados + sus instalaciones/socorristas  
- **coordinador**: Ve solo sus instalaciones/socorristas

---

## Current Status / Progress Tracking

### ✅ **TAREA COMPLETADA: GIT REVERT Y DOCUMENTACIÓN DE BASE DE DATOS**

**📅 Fecha:** 2025-01-12  
**🎯 Tarea:** Revert a commit a86e2c8 y crear documentación completa de BD

**PROCESO EJECUTADO:**
1. ✅ **Git revert exitoso** - Código revertido a commit `a86e2c8` (Bug fix Botiquín)
2. ✅ **Creación de documentación completa** - Análisis de 11 tablas de la BD
3. ✅ **Estrategia de branch segura** - Opción 1 ejecutada perfectamente
4. ✅ **Conversión a rama principal** - `main` actualizado con nueva documentación
5. ✅ **Limpieza del repositorio** - Ramas temporales eliminadas

**ARCHIVOS CREADOS:**
- `database/database_structure.md` - Documentación completa de estructura BD
- `database/fix_admin_types.sql` - Script para correcciones admin

**ESTADO FINAL:**
- **Commit actual:** `db7b59b` (Add database documentation and fix admin types)
- **Base de código:** Estable en commit `a86e2c8` + documentación nueva
- **Repositorio:** Sincronizado perfectamente (local ↔ remoto)

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
