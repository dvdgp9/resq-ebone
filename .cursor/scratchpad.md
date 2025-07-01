# ResQ - Sistema de Gestión de Socorristas

## Background and Motivation

**Proyecto**: ResQ - Sistema web PHP para gestión de socorristas en instalaciones acuáticas
**URL**: https://resq.ebone.es  
**Objetivo**: Sistema completo con funcionalidades para socorristas y administradores

### 🔍 **ANÁLISIS ACTUAL DEL SISTEMA DE ADMINISTRACIÓN** (Enero 2025)

**ESTRUCTURA ACTUAL**:
- ✅ **Sistema de Autenticación Admin**: Implementado con `AdminAuthService` y sesiones independientes
- ✅ **Panel Dashboard**: Dashboard completo con estadísticas y navegación a todas las secciones
- ✅ **CRUD Completo**: Coordinadores, Instalaciones, Socorristas (vía `AdminService`)
- ✅ **Sistema de Informes**: Exportación avanzada con filtros
- ✅ **Base de Datos**: Tablas `admins` y `admin_sesiones` configuradas

**CONTROLADORES ADMIN** (`controllers/admin/`):
- `auth.php` - Autenticación (login/logout)
- `coordinadores.php` - Gestión de coordinadores
- `instalaciones.php` - Gestión de instalaciones  
- `socorristas.php` - Gestión de socorristas
- `informes.php` - Sistema de reportes y exportación

**VISTAS ADMIN** (`views/admin/`):
- `login.php` - Página de login admin
- `dashboard.php` - Panel principal con estadísticas
- `coordinadores.php` - CRUD coordinadores (663 líneas)
- `instalaciones.php` - CRUD instalaciones (802 líneas)
- `socorristas.php` - CRUD socorristas (495 líneas)
- `informes.php` - Página de informes (247 líneas)

**FUNCIONALIDADES IDENTIFICADAS**:
- 🔐 Login/logout independiente del sistema de socorristas
- 📊 Dashboard con estadísticas en tiempo real
- 👥 Gestión completa de coordinadores (crear, editar, eliminar)
- 🏢 Gestión de instalaciones con espacios y aforos
- 🚑 Gestión de socorristas asignados a instalaciones
- 📈 Sistema de informes con exportación CSV/Excel
- ⚡ Acciones rápidas desde dashboard

**OBSERVACIONES PARA MEJORA**:
- 🎨 **Headers inconsistentes**: Admin usa headers propios vs. socorristas con `header-universal.php`
- 📱 **Responsive**: Dashboard admin no sigue el patrón mobile-first del resto de la app
- 🔄 **Consistencia UI**: Estilos admin difieren del diseño unificado de socorristas
- 📋 **Navegación**: Falta navegación breadcrumb entre secciones admin
- ⚠️ **Seguridad**: Verificar permisos granulares por tipo de admin

## Project Status Board

### ✅ **FASES COMPLETADAS**
- [x] **Gestión de Espacios**: Sistema completo CRUD con interfaz admin
- [x] **Exportación/Informes**: 3 tipos CSV con filtros avanzados y compatibilidad Excel
- [x] **Nuevo Control de Flujo**: Basado en espacios con valores inteligentes
- [x] **Sistema de Botiquín**: Gestión manual completa con revisiones diarias
- [x] **Rediseño Dashboard Móvil**: Header, tarjetas horizontales y footer navegación
- [x] **Rediseño UI/UX Formularios**: Diseño minimalista mobile-first
- [x] **Unificación Headers Socorristas**: Componente universal para experiencia consistente
- [x] **Bug Fix Emails Botiquín**: Corregido error JSON en solicitudes de material
- [x] **Ordenamiento Botiquín**: Elementos mostrados por orden alfabético

### 🔄 **PRÓXIMAS FASES**
- [ ] **Mejoras Panel Admin**: Pendiente de definir (En análisis - Enero 2025)
- [ ] **Futuras mejoras**: Según necesidades del usuario

## Current Status / Progress Tracking

**⚡ ESTADO ACTUAL: INVESTIGANDO CONSULTAS SQL EXPORTACIÓN**

### 🎯 **FUNCIONALIDADES ACTIVAS**:

**SISTEMA SOCORRISTAS (100% Completado)**:
- **Dashboard móvil**: Diseño renovado con navegación footer
- **Control de Flujo**: Por espacios con exportación dinámica
- **Reporte de Incidencias**: Con envío de emails automático
- **Gestión de Botiquín**: Inventario ordenado alfabéticamente + solicitudes de material
- **Mi Cuenta**: Información personal y cambio de contraseña
- **Headers universales**: Experiencia consistente en todas las páginas

**SISTEMA ADMINISTRACIÓN (Análisis Completado)**:
- **Panel de Login**: Autenticación independiente funcional
- **Dashboard Admin**: Estadísticas y navegación principal
- **CRUD Coordinadores**: Gestión completa de coordinadores
- **CRUD Instalaciones**: Gestión de instalaciones y espacios
- **CRUD Socorristas**: Asignación a instalaciones
- **Sistema de Informes**: Exportación avanzada con filtros
- **Base de Datos**: Estructura admin completamente implementada

### 🧪 **TESTING REQUERIDO**:

**Sistema Socorristas**:
- Navegación entre formularios para verificar headers universales
- Ordenamiento alfabético del botiquín
- Funcionalidad de emails en incidencias y botiquín

**Sistema Administración**:
- Login admin con credenciales correctas e incorrectas
- Navegación entre todas las secciones del panel admin
- CRUD completo de coordinadores, instalaciones y socorristas
- Exportación de informes con diferentes filtros
- Responsive design en dispositivos móviles
- Consistencia visual entre secciones admin

## Lessons

### Lecciones Técnicas Clave
- **Componentes reutilizables**: Usar parciales PHP mejora mantenibilidad drásticamente
- **Responsive mobile-first**: Approach más seguro para interfaces modernas
- **Emails de sistema**: Reutilizar métodos existentes en lugar de crear funciones custom
- **Ordenamiento localizado**: `localeCompare()` con configuración española para ordenamiento alfabético correcto
- **Limpieza de código**: Eliminar funciones obsoletas y referencias DOM inexistentes
- **Design system**: Mantener consistencia visual con componentes universales
- **Implementación incremental**: Pasos pequeños con confirmación mejoran control de calidad
- **Codificación UTF-8 en CSV**: Eliminar BOM y mb_convert_encoding 'auto' previene corrupción de caracteres especiales (ñ, acentos)

## Executor's Feedback or Assistance Requests

**Estado**: 🔍 **MODO PLANNER - ANÁLISIS PROBLEMA CODIFICACIÓN CSV**

## Key Challenges and Analysis

### 🚨 **PROBLEMA PERSISTENTE IDENTIFICADO**
A pesar de las correcciones realizadas en `generateCSV()`, el problema de codificación persiste:
- **Síntomas actuales**: "MarÃ-a GarcÃ-a" en lugar de "María García", "PVÃ©rez" en lugar de "Pérez"
- **Conclusión**: El problema NO está en la función de generación CSV, sino en una etapa anterior

### 🔍 **ANÁLISIS DE POSIBLES CAUSAS RAÍZ**

**1. CODIFICACIÓN EN BASE DE DATOS** 
- ❓ **Hipótesis alta**: Los datos ya están corruptos en la BD desde el momento de inserción
- ❓ **Verificación necesaria**: Consultar directamente la BD para ver si los nombres están mal almacenados
- ❓ **Posible causa**: Conexión PDO sin charset UTF-8 configurado

**2. CODIFICACIÓN EN CONSULTAS SQL**
- ❓ **Hipótesis media**: La consulta SQL no está configurada para UTF-8
- ❓ **Verificación necesaria**: Revisar configuración PDO en Database.php
- ❓ **Solución potencial**: Añadir `SET NAMES utf8mb4` en conexión

**3. CODIFICACIÓN EN INSERCIÓN DE DATOS**
- ❓ **Hipótesis media**: Los formularios de creación de socorristas/coordinadores no manejan UTF-8
- ❓ **Verificación necesaria**: Revisar controladores de creación de usuarios
- ❓ **Solución potencial**: Validar que los formularios usen `accept-charset="UTF-8"`

**4. CONFIGURACIÓN DE SERVIDOR/PHP**
- ❓ **Hipótesis baja**: Configuración PHP no manejando UTF-8 correctamente
- ❓ **Verificación necesaria**: Revisar php.ini y configuración de servidor
- ❓ **Solución potencial**: `ini_set('default_charset', 'UTF-8')`

### 📋 **PLAN DE INVESTIGACIÓN PROPUESTO**

**FASE 1: DIAGNÓSTICO DE ORIGEN DE DATOS**
1. Consultar directamente la BD para verificar si los datos están corruptos en origen
2. Revisar configuración de conexión PDO en `config/database.php` o clase Database
3. Verificar headers de los formularios de creación de usuarios

**FASE 2: CORRECCIÓN SEGÚN DIAGNÓSTICO**
- Si problema en BD: Corregir configuración PDO y considerar migración de datos
- Si problema en inserción: Corregir formularios y procesos de inserción
- Si problema persiste: Investigar configuración PHP/servidor

**FASE 3: VALIDACIÓN**
- Crear usuario de prueba con caracteres especiales
- Verificar almacenamiento correcto en BD
- Probar exportación CSV completa

## High-level Task Breakdown

### 🎯 **TAREAS PRIORIZADAS**

**Tarea 1: Diagnóstico Base de Datos**
- [ ] Consultar tabla `socorristas` directamente para verificar codificación de nombres
- [ ] Revisar configuración PDO en clase Database
- [ ] Verificar collation de tablas de BD
- **Criterio de éxito**: Identificar si el problema está en almacenamiento o recuperación

**Tarea 2: Revisar Proceso de Inserción**  
- [ ] Examinar formularios de creación de coordinadores/socorristas
- [ ] Verificar headers y charset de formularios HTML
- [ ] Revisar controladores de procesamiento de formularios
- **Criterio de éxito**: Confirmar que inserción maneja UTF-8 correctamente

**Tarea 3: Implementar Corrección**
- [ ] Aplicar corrección según diagnóstico (PDO config, formularios, o ambos)
- [ ] Crear script de migración de datos si es necesario
- [ ] Validar corrección con datos de prueba
- **Criterio de éxito**: Nuevos datos se almacenan y exportan correctamente

**Tarea 4: Validación Final**
- [ ] Probar exportación CSV con datos corregidos
- [ ] Verificar que no hay regresiones en otras funcionalidades
- [ ] Documentar solución en lessons aprendidas
- **Criterio de éxito**: CSV exporta nombres con acentos correctamente 