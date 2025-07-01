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

**🎯 ESTADO ACTUAL: INTENTO 3 - TEXT/PLAIN UTF-8 CON BOM**

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
- **Charset en consultas JOIN**: Añadir `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci` antes de consultas con JOIN previene corrupción en recuperación de datos
- **Excel y UTF-8**: Excel requiere BOM UTF-8 (`\xEF\xBB\xBF`) para interpretar correctamente caracteres especiales. Preview/otros lectores CSV no lo necesitan
- **Excel problemático con UTF-8**: Cuando BOM falla, convertir a Windows-1252 usando `mb_convert_encoding()` garantiza compatibilidad total con Excel
- **Windows-1252 puede empeorar**: Conversión UTF-8→Windows-1252 puede crear "MarÍa" en lugar de "María". Mejor mantener UTF-8 y cambiar Content-Type a text/plain

## Executor's Feedback or Assistance Requests

**Estado**: 🎯 **ESTRATEGIA 3: CSV COMO TEXT/PLAIN + UTF-8**

### 🎯 **ANÁLISIS DE INTENTOS FALLIDOS**

**HISTÓRICO DE INTENTOS**:
- ❌ **Intento 1**: UTF-8 + BOM con `application/csv` → Excel seguía mostrando caracteres corruptos
- ❌ **Intento 2**: Windows-1252 → Empeoró: "MarÍa GarcÍa PÉrez" (peor que antes)
- 🎯 **Intento 3**: UTF-8 + BOM con `text/plain` (nuevo enfoque)

**NUEVA ESTRATEGIA IMPLEMENTADA**:
- 🔄 **Content-Type cambiado**: `text/plain; charset=UTF-8` en lugar de `application/csv`
- ✅ **Volver a UTF-8**: Eliminada conversión Windows-1252 que empeoró las cosas
- ✅ **BOM UTF-8 restaurado**: `\xEF\xBB\xBF` de nuevo
- ✅ **Sin conversiones**: Solo `trim()`, manteniendo datos originales

### 📋 **CÓDIGO TEXT/PLAIN PARA EXCEL**

```php
function generateCSV($data, $filename) {
    // Headers alternativos - CSV como texto plano
    header('Content-Type: text/plain; charset=UTF-8');
    
    // BOM UTF-8 con text/plain
    fwrite($output, "\xEF\xBB\xBF");
    
    // Sin conversiones, datos originales
    $cleanRow = array_map('trim', $row);
}
```

### 🧪 **TEORÍA**
Excel podría interpretar mejor UTF-8 cuando viene etiquetado como `text/plain` en lugar de `application/csv` 