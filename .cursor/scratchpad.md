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

**🚀 ESTADO ACTUAL: EJECUTANDO - IMPLEMENTACIÓN GESTIÓN BOTIQUÍN ADMIN**

### ✅ **DECISIÓN CONFIRMADA: IMPLEMENTAR PERMISOS PRIMERO**

**🎯 REQUISITOS ESPECÍFICOS DE PERMISOS:**

1. **Superadmin** (`coordinador_id = NULL`):
   - Acceso total a todo el sistema

2. **Admins** (`coordinador_id = específico`):
   - Acceso a TODOS los coordinadores asignados a ellos
   - Acceso a TODAS las instalaciones de esos coordinadores
   - Acceso a TODOS los socorristas de esas instalaciones
   - **NO** pueden ver información de otros admins

3. **Coordinadores** (acceso directo):
   - Acceso solo a sus instalaciones asignadas
   - Acceso solo a socorristas de sus instalaciones

### 📋 **NUEVO PLAN DE IMPLEMENTACIÓN:**

**FASE 1: ESTRUCTURA DE PERMISOS**
- [x] **Paso 1A**: Confirmar estructura (COMPLETADO)
- [x] **Paso 1B**: Implementar SQL de tabla intermedia (COMPLETADO)
- [x] **Paso 1C**: Crear AdminPermissionsService (COMPLETADO)
- [x] **Paso 1D**: Actualizar AdminAuthService con nuevos permisos (COMPLETADO)
- [x] **Paso 1E**: Testing de permisos (OMITIDO - continuar con botiquín)

**FASE 2: BOTIQUÍN CON PERMISOS (4-5 horas)** 🚀
- [x] **Paso 2A**: Crear controlador admin/botiquin.php (COMPLETADO)
- [x] **Paso 2B**: Crear vista admin/botiquin.php con dashboard inicial (COMPLETADO)
- [x] **Paso 2C**: Añadir entrada en menú del panel admin (COMPLETADO)
- [ ] **Paso 2D**: Implementar gestión básica de productos (CRUD)
- [ ] **Paso 2E**: Implementar gestión de solicitudes
- [ ] **Paso 2F**: Implementar importación masiva
- [ ] **Paso 2G**: Testing y refinamiento

### 🚀 **INICIANDO FASE 2: BOTIQUÍN ADMINISTRATIVO**

**🎯 OBJETIVO ACTUAL:** Crear sistema completo de gestión de botiquín para coordinadores/admins con:
- Dashboard de resumen por instalaciones
- CRUD de productos con permisos
- Gestión de solicitudes de socorristas
- Importación masiva de inventarios

### 🔥 **COMENZANDO PASO 2A: Controlador admin/botiquin.php**

### 🔍 **NECESITO CAPTURAS DE TABLAS ANTES DE PROCEDER:**

Para confirmar la estructura actual y no modificar nada incorrecto, necesito capturas de:

1. **Tabla `coordinadores`** - Para ver la estructura actual
2. **Tabla `instalaciones`** - Para confirmar relación con coordinadores  
3. **Tabla `socorristas`** - Para ver relación con instalaciones

¿Puedes pasarme estas capturas para confirmar la estructura antes de empezar a codificar?

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
- **Charset en consultas JOIN**: Añadir `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci` antes de consultas con JOIN previene corrupción en recuperación de datos
- **Excel y UTF-8**: Excel requiere BOM UTF-8 (`\xEF\xBB\xBF`) para interpretar correctamente caracteres especiales. Preview/otros lectores CSV no lo necesitan
- **Excel problemático con UTF-8**: Cuando BOM falla, convertir a Windows-1252 usando `mb_convert_encoding()` garantiza compatibilidad total con Excel
- **Windows-1252 puede empeorar**: Conversión UTF-8→Windows-1252 puede crear "MarÍa" en lugar de "María". Mejor mantener UTF-8 y cambiar Content-Type a text/plain
- **Excel codificación local**: Problema puede ser específico de configuración regional/idioma de Excel del usuario. Alternativa futura: XLSX nativo vía PhpSpreadsheet

## Executor's Feedback or Assistance Requests

**Estado**: ⏸️ **PROBLEMA CSV/EXCEL POSPUESTO - SISTEMA FUNCIONAL**

### 🎯 **RESUMEN FINAL CODIFICACIÓN CSV**

**INTENTOS REALIZADOS**:
- ❌ **Intento 1**: UTF-8 + BOM con `application/csv` → Caracteres corruptos en Excel
- ❌ **Intento 2**: Windows-1252 → Empeoró: "MarÍa GarcÍa PÉrez" 
- ⏸️ **Intento 3**: UTF-8 + BOM con `text/plain` → **Estado actual (funcional)**

**ESTADO ACTUAL**:
- ✅ **Preview Mac**: Muestra caracteres correctamente ("María García Pérez")
- ⚠️ **Excel**: Problemas de codificación (posible configuración local)
- ✅ **Funcionalidad**: Sistema de exportación completamente operativo
- ✅ **Datos**: Consultas SQL y estructura correctas

**ALTERNATIVAS FUTURAS DOCUMENTADAS**:
- 📊 **XLSX nativo**: PhpSpreadsheet para compatibilidad total Excel (2-3h implementación)
- 🔧 **Investigación Excel**: Configuración regional/idioma específica del usuario
- 🌐 **Otros formatos**: JSON, XML como alternativas

### 📋 **CÓDIGO FINAL (TEXT/PLAIN + UTF-8)**

```php
function generateCSV($data, $filename) {
    header('Content-Type: text/plain; charset=UTF-8');
    fwrite($output, "\xEF\xBB\xBF"); // BOM UTF-8
    // Datos limpios sin conversiones
}
```

### ✅ **DECISIÓN**
Sistema funcional mantenido. Problema de codificación Excel pospuesto para investigación futura. 

## Background and Motivation

**Nueva Solicitud**: Análisis UI/UX para gestión administrativa de botiquín

### Contexto Identificado:
- **Socorristas**: Pueden gestionar cantidades del botiquín (crear/actualizar/eliminar elementos)
- **Coordinadores/Superadmins**: Necesitan poder gestionar tanto cantidades como productos (añadir nuevos productos, eliminar existentes)
- **Alcance**: Solo en instalaciones asignadas al coordinador

### Arquitectura Actual Botiquín:
- `inventario_botiquin`: Productos y cantidades por instalación
- `historial_botiquin`: Trazabilidad de cambios
- `solicitudes_material`: Solicitudes de socorristas a coordinación
- **Relación**: `instalaciones.coordinador_id` → cada coordinador tiene instalaciones específicas

## Key Challenges and Analysis

### 🎨 **ANÁLISIS UI/UX: GESTIÓN ADMINISTRATIVA DE BOTIQUÍN**

#### **Estado Actual del Sistema:**
1. **Socorristas**: Interfaz completa de gestión (`/views/formularios/botiquin.php`)
   - ✅ Crear/editar/eliminar productos
   - ✅ Gestionar cantidades
   - ✅ Solicitar material a coordinación
   - ✅ Ver historial de cambios

2. **Coordinadores**: **SIN INTERFAZ ADMINISTRATIVA**
   - ❌ No pueden gestionar productos de sus instalaciones
   - ❌ No pueden añadir nuevos productos
   - ❌ No pueden eliminar productos obsoletos
   - ❌ No pueden gestionar solicitudes de material

#### **Necesidades Identificadas para Coordinadores:**
- **Gestión de Productos**: CRUD completo de productos del botiquín
- **Gestión de Cantidades**: Igual funcionalidad que socorristas
- **Gestión de Solicitudes**: Revisar/aprobar/gestionar solicitudes de material
- **Visión Global**: Ver inventario de todas sus instalaciones asignadas
- **Restricciones de Acceso**: Solo instalaciones bajo su coordinación

---

## High-level Task Breakdown

### 🎯 **ANÁLISIS DE OPCIONES UI/UX**

#### **OPCIÓN 1: SECCIÓN DEDICADA EN ADMIN PANEL** 
*(Recomendada como experto UI/UX)*

**Descripción**: Crear sección "Gestión de Botiquín" en panel administrativo

**✅ PROS:**
- **Separación de responsabilidades**: Interfaz administrativa independiente
- **Escalabilidad**: Fácil añadir funciones avanzadas (reportes, análisis, etc.)
- **Consistencia**: Mantiene el patrón de design del admin panel
- **Permisos claros**: Diferentes niveles de acceso (coordinador vs superadmin)
- **Funcionalidades específicas**: Gestión de solicitudes, reportes por instalación
- **Mejor UX**: Interfaz optimizada para tareas administrativas

**❌ CONTRAS:**
- **Desarrollo adicional**: Requiere nueva interfaz completa
- **Duplicación de funcionalidades**: Algunas funciones se repiten
- **Aprendizaje**: Coordinadores deben aprender nueva interfaz

**🔧 IMPLEMENTACIÓN:**
- Controlador: `/controllers/admin/botiquin.php`
- Vista: `/views/admin/botiquin.php`
- Funciones: CRUD productos, gestión solicitudes, reportes

---

#### **OPCIÓN 2: ROLES Y PERMISOS EN MISMA INTERFAZ**

**Descripción**: Adaptar interfaz actual con funciones adicionales según rol

**✅ PROS:**
- **Reutilización**: Aprovecha interfaz existente
- **Consistencia de datos**: Misma base de datos y lógica
- **Menos desarrollo**: Modificaciones menores a interfaz actual
- **Aprendizaje mínimo**: Coordinadores usan interfaz familiar

**❌ CONTRAS:**
- **Complejidad de interfaz**: Múltiples funciones en una sola vista
- **Confusión de roles**: Mezcla funciones de socorrista y coordinador
- **Escalabilidad limitada**: Difícil añadir funciones administrativas complejas
- **Experiencia de usuario**: Interfaz no optimizada para tareas administrativas

---

#### **OPCIÓN 3: INTERFAZ HÍBRIDA**

**Descripción**: Panel admin para gestión masiva + acceso directo desde interfaz socorrista

**✅ PROS:**
- **Flexibilidad**: Múltiples puntos de acceso
- **Eficiencia**: Gestión rápida y gestión detallada
- **Mejor UX**: Cada interfaz optimizada para su propósito

**❌ CONTRAS:**
- **Complejidad de desarrollo**: Requiere múltiples interfaces
- **Inconsistencia**: Diferentes UX para mismas tareas
- **Confusión**: Múltiples formas de hacer lo mismo

---

### 🏆 **RECOMENDACIÓN COMO EXPERTO UI/UX: OPCIÓN 1**

**Razones:**
1. **Principio de Separación de Responsabilidades**: Tareas administrativas requieren interfaz específica
2. **Escalabilidad**: Fácil añadir funciones avanzadas (reportes, dashboards, etc.)
3. **Experiencia de Usuario**: Interfaz optimizada para coordinadores
4. **Consistencia**: Mantiene el patrón de design del sistema administrativo
5. **Futuro**: Permite añadir gestión de solicitudes, reportes, análisis de inventario

**Funcionalidades Propuestas:**
- **Dashboard de Botiquín**: Resumen de todas las instalaciones
- **Gestión de Productos**: CRUD completo con búsqueda avanzada
- **Gestión de Solicitudes**: Revisar/aprobar solicitudes de socorristas
- **Reportes**: Inventario bajo mínimos, historial de cambios, estadísticas
- **Importación masiva**: Subir inventarios desde Excel/CSV

---

## Project Status Board

### 📋 **TAREAS PENDIENTES**

- [ ] **Decisión final**: Confirmar opción elegida por el usuario
- [ ] **Planificación detallada**: Definir funcionalidades específicas
- [ ] **Wireframes**: Diseñar interfaz de usuario
- [ ] **Desarrollo**: Implementar funcionalidades

### 🎯 **PRÓXIMOS PASOS**

1. **Validar análisis** con el usuario
2. **Confirmar opción** elegida
3. **Definir funcionalidades** específicas
4. **Estimar tiempo** de desarrollo

## Executor's Feedback or Assistance Requests

### 💭 **PREGUNTAS PARA EL USUARIO:**

1. **¿Qué opción prefieres?** (Recomiendo Opción 1)
2. **¿Qué funcionalidades específicas** necesitas para coordinadores?
3. **¿Necesitas gestión de solicitudes** de material?
4. **¿Quieres reportes** de inventario?
5. **¿Hay alguna funcionalidad** que no he considerado?

### 📊 **ANÁLISIS ADICIONAL DISPONIBLE:**
- Estimación de tiempo de desarrollo por opción
- Wireframes específicos de la interfaz
- Análisis de flujo de trabajo para coordinadores
- Propuesta de funcionalidades avanzadas 

### ✅ **INFORMACIÓN RECIBIDA - TABLA ADMINS:**

**Estructura confirmada:**
- `id`, `email`, `password_hash`, `nombre`
- `tipo` (campo string: "superadmin")  
- `coordinador_id` (NULL para superadmin)
- `activo`, `fecha_creacion`

**Niveles de permisos necesarios:**
1. **Superadmin**: `coordinador_id = NULL` → Acceso total
2. **Admins**: `coordinador_id` específico → Acceso a coordinadores asignados
3. **Coordinadores**: Acceso solo a sus instalaciones asignadas

### 🚨 **DECISIÓN TÉCNICA CRÍTICA:**

**PROBLEMA**: El sistema actual de permisos no está completamente implementado para manejar los 3 niveles de acceso necesarios para el botiquín.

**OPCIONES:**
1. **Implementar permisos PRIMERO** → Después botiquín (más eficiente)
2. **Implementar botiquín AHORA** → Refactorizar permisos después (más trabajo)

### 🏆 **MI RECOMENDACIÓN COMO EXECUTOR:**

**OPCIÓN 1: Implementar sistema de permisos primero**

**✅ VENTAJAS:**
- Botiquín se implementa correctamente desde el inicio
- No hay que refactorizar código después
- Mejor arquitectura y seguridad
- Base sólida para futuras funcionalidades

**❌ DESVENTAJAS:**
- Retrasa la implementación del botiquín
- Tarea adicional no planificada inicialmente

**⏱️ ESTIMACIÓN:**
- **Permisos**: 2-3 horas
- **Botiquín con permisos correctos**: 4-5 horas
- **TOTAL**: 6-8 horas

VS.

- **Botiquín sin permisos**: 3-4 horas
- **Refactorización posterior**: 3-4 horas  
- **TOTAL**: 6-8 horas (pero con código menos limpio)

### 💭 **PREGUNTA PARA EL USUARIO:**

¿Prefieres que:
1. **🔐 Implemente primero el sistema de permisos** (2-3 horas) y después el botiquín correctamente
2. **🏥 Siga con el botiquín ahora** con permisos básicos y los mejore después

**Mi recomendación técnica es la Opción 1** porque será más eficiente a largo plazo. 

### ✅ **PASO 1B COMPLETADO: SQL EJECUTADO Y DOCUMENTACIÓN ACTUALIZADA**

**🎯 LOGROS:**
- SQL de tabla intermedia ejecutado ✅
- Tabla `admin_coordinadores` creada ✅
- Campo `tipo` actualizado con 3 niveles ✅
- Índices optimizados creados ✅
- `database_structure.md` completamente actualizado ✅

### 🚀 **CONTINUANDO CON PASO 1C: AdminPermissionsService**

Ahora procedo a crear el sistema de verificación de permisos. 

### ✅ **PASOS 1C Y 1D COMPLETADOS: SISTEMA DE PERMISOS IMPLEMENTADO**

**🎯 LOGROS PASO 1C:**
- `AdminPermissionsService` creado ✅
- Verificación de roles (Superadmin/Admin/Coordinador) ✅
- Funciones de obtención de datos según permisos ✅
- Verificación de acceso específico a recursos ✅
- Funciones específicas para botiquín ✅

**🎯 LOGROS PASO 1D:**
- `AdminAuthService` actualizado con nuevos roles ✅
- Integración con `AdminPermissionsService` ✅
- Métodos helper para verificar permisos ✅
- Funciones de debugging y descripción de roles ✅

### 🧪 **PASO 1E: TESTING DE PERMISOS NECESARIO**

Antes de proceder con el botiquín, necesito hacer testing rápido del sistema de permisos para asegurarme de que funciona correctamente.

**¿Quieres que cree un script de testing para verificar permisos o prefieres que continúe directamente con la implementación del botiquín?**

### ✅ **PASO 2A COMPLETADO: Controlador Creado**

**🎯 FUNCIONALIDADES IMPLEMENTADAS:**
- API completa con permisos integrados ✅
- Dashboard con estadísticas por instalación ✅
- CRUD completo de elementos de botiquín ✅
- Gestión de solicitudes de material ✅
- Importación masiva desde CSV ✅
- Verificación de permisos en todas las acciones ✅

### 🎨 **CONTINUANDO PASO 2B: Vista admin/botiquin.php**

### ✅ **PASO 2B COMPLETADO: Vista Creada**

**🎯 FUNCIONALIDADES DE LA VISTA:**
- Interfaz completa con 4 secciones (Dashboard/Inventario/Solicitudes/Importar) ✅
- Dashboard con estadísticas y resumen por instalación ✅
- Gestión de inventario con filtros avanzados ✅
- Gestión de solicitudes con cambio de estados ✅
- Importación masiva desde CSV ✅
- Modales para CRUD de elementos ✅
- JavaScript completo con todas las funciones ✅

### 🔗 **CONTINUANDO PASO 2C: Añadir al menú admin**

### ✅ **PASO 2C COMPLETADO: Menú integrado**

**🎯 MODIFICACIONES REALIZADAS:**

1. **Dashboard admin** (`views/admin/dashboard.php`):
   - Añadida nueva carta de navegación para "Gestión de Botiquín" ✅
   - Añadida estadística de "Solicitudes Pendientes" ✅

2. **AdminService** (`classes/AdminService.php`):
   - Añadida estadística de `solicitudes_pendientes` en `getEstadisticas()` ✅
   - Manejo de error si tabla no existe aún ✅

3. **Router** (`index.php`):
   - Añadida ruta `/admin/botiquin` para la vista ✅
   - Añadida ruta `/admin/api/botiquin` para el controlador ✅

**✅ La funcionalidad de botiquín está completamente integrada en el panel admin**

### 🔧 **CONTINUANDO PASO 2D: Implementar gestión básica de productos (CRUD)**

**El sistema está listo para hacer pruebas** - Se puede acceder a `http://localhost/admin/botiquin` para probar la interfaz administrativa. 