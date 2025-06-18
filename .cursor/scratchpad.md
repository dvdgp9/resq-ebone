# ResQ - Aplicación para Socorristas
**Dominio de despliegue**: resq.ebone.es

## Background and Motivation

ResQ es una aplicación web PHP completa para gestión de socorristas desplegada en `resq.ebone.es`. El sistema cuenta con:

**✅ COMPLETADO ANTERIORMENTE:**
- Panel de administración completo (Coordinadores, Instalaciones, Socorristas)
- Sistema de autenticación y sesiones
- 3 formularios funcionales: Control Flujo, Incidencias, Parte Accidente (con notificaciones email)
- Diseño consistente con tema naranja socorrista y logo implementado

**🚀 NUEVA FASE:** Implementación de funcionalidades avanzadas de gestión e informes.

## Key Challenges and Analysis

### ✅ ANÁLISIS TÉCNICO COMPLETADO - FORMULARIOS JSON:
Los formularios actuales se almacenan correctamente en `tabla formularios` con `datos_json` tipo JSON, lo que permite exportación directa a Excel por columnas sin modificar el procesamiento existente.

### 🔧 NUEVAS MODIFICACIONES ESTRUCTURALES REQUERIDAS:
1. **Tabla instalaciones**: Añadir campos `espacios` (JSON) y `aforo_maximo` (INT nullable)
2. **Nuevas tablas botiquín**: `botiquin_elementos` y `botiquin_checks`
3. **Reemplazar formulario control_flujo**: Nuevo sistema por espacios y franjas horarias

## High-level Task Breakdown

### 🏗️ FASE 1: MODIFICACIONES BASE DE DATOS Y INSTALACIONES
**Objetivo**: Preparar estructura para las nuevas funcionalidades

#### Subtarea 1.1: Modificar tabla instalaciones ⚡
- [📋] Añadir campo `espacios` (JSON) para almacenar espacios personalizables
- [📋] Añadir campo `aforo_maximo` (INT nullable) para cálculo de porcentajes
- [📋] Crear migración SQL para actualizar tabla existente
- [📋] **Criterio éxito**: Tabla modificada sin perder datos existentes

#### Subtarea 1.2: Actualizar gestión de instalaciones 🔧
- [📋] Modificar `views/admin/instalaciones.php` para incluir gestión de espacios
- [📋] Añadir campo aforo máximo al formulario de instalaciones
- [📋] Actualizar `AdminService.php` para manejar espacios y aforo
- [📋] Implementar CRUD de espacios (añadir, editar, eliminar con histórico)
- [📋] **Criterio éxito**: Gestión completa de espacios desde panel admin

### 📊 FASE 2: HERRAMIENTA DE INFORMES
**Objetivo**: Sistema de exportación Excel con filtros

#### Subtarea 2.1: Backend de exportación ⚙️
- [📋] Crear `controllers/admin/informes.php` para API de exportación
- [📋] Implementar generación Excel desde JSON (usar PhpSpreadsheet si no existe composer alternativo)
- [📋] Funciones de filtrado: instalación, socorrista, rango fechas
- [📋] Cálculos específicos: suma personas, porcentaje aforo
- [📋] **Criterio éxito**: API funcional que genera Excel desde datos JSON

#### Subtarea 2.2: Frontend modal de informes 🎨
- [📋] Crear modal de exportación en dashboard de admin
- [📋] Formulario con selectores: tipo formulario, instalación, socorrista, fechas
- [📋] Botón de descarga que llama a API y descarga Excel
- [📋] Feedback visual durante generación del reporte
- [📋] **Criterio éxito**: Modal funcional que descarga Excel correctamente

### 👥 FASE 3: NUEVO CONTROL FLUJO PERSONAL POR ESPACIOS
**Objetivo**: Sistema de control de aforo por espacios y franjas horarias

#### Subtarea 3.1: Nuevo formulario control flujo 📝
- [📋] Reemplazar `views/formularios/control_flujo.php` completamente
- [📋] Modal con franjas de media hora exactas (dropdown)
- [📋] Mostrar espacios de la instalación del socorrista
- [📋] Campos numéricos para cantidad de personas por espacio
- [📋] **Criterio éxito**: Formulario funcional con espacios dinámicos

#### Subtarea 3.2: Backend nuevo control flujo ⚙️
- [📋] Actualizar `controllers/control_flujo.php` para nueva estructura JSON
- [📋] Validaciones: franjas horarias válidas, cantidades por espacio
- [📋] Mantener compatibilidad con histórico (datos antiguos)
- [📋] **Criterio éxito**: Sistema almacena correctamente datos por espacios

### 🏥 FASE 4: SISTEMA CHEQUEO MATERIAL BOTIQUÍN
**Objetivo**: Gestión diaria de inventario de botiquín

#### Subtarea 4.1: Crear tablas botiquín 🗄️
- [📋] Tabla `botiquin_elementos` (instalacion_id, elemento, cantidad_minima)
- [📋] Tabla `botiquin_checks` (instalacion_id, socorrista_id, fecha, elementos_json)
- [📋] Migración SQL para crear tablas
- [📋] **Criterio éxito**: Tablas creadas y relacionadas correctamente

#### Subtarea 4.2: Gestión elementos botiquín (Admin) 🔧
- [📋] Vista admin para gestionar elementos por instalación
- [📋] CRUD de elementos: añadir, editar, eliminar
- [📋] Configuración de cantidades mínimas
- [📋] **Criterio éxito**: Admin puede gestionar inventario base por instalación

#### Subtarea 4.3: Formulario check diario (Socorristas) ✅
- [📋] Nueva vista `views/formularios/check_botiquin.php`
- [📋] Tabla con elementos de la instalación y cantidades actuales
- [📋] Campos editables para actualizar cantidades
- [📋] Botón "Solicitar Material" que marca elementos y envía email
- [📋] **Criterio éxito**: Check diario funcional con solicitud por email

## Project Status Board

### 🎯 ESTADO ACTUAL: PLANIFICACIÓN COMPLETADA
**Siguiente paso**: Iniciar Fase 1 con modificaciones de base de datos

#### 📋 TAREAS PENDIENTES (Por orden de ejecución):
- [ ] **FASE 1.1**: Modificar tabla instalaciones (espacios + aforo_maximo)
- [ ] **FASE 1.2**: Actualizar gestión de instalaciones en admin
- [ ] **FASE 2.1**: Backend de exportación Excel
- [ ] **FASE 2.2**: Frontend modal de informes
- [ ] **FASE 3.1**: Nuevo formulario control flujo
- [ ] **FASE 3.2**: Backend nuevo control flujo
- [ ] **FASE 4.1**: Crear tablas botiquín
- [ ] **FASE 4.2**: Gestión elementos botiquín (Admin)
- [ ] **FASE 4.3**: Formulario check diario (Socorristas)

### 🎛️ CONFIGURACIÓN CONFIRMADA:
- **Espacios**: Completamente personalizables por instalación
- **Histórico**: Se mantiene al modificar espacios
- **Franjas**: Media hora exactas (09:00, 09:30, 10:00...)
- **Control flujo actual**: Se reemplaza completamente
- **Informes**: Suma personas + porcentaje aforo + filtros básicos
- **Botiquín**: Elementos por instalación + cantidades exactas + solicitud manual

## Current Status / Progress Tracking

**PLANNER STATUS**: ✅ Planificación completada y aprobada por usuario

**Respuestas de clarificación recibidas y documentadas**:
- Control Flujo Personal: Espacios personalizables, histórico mantenido, franjas exactas
- Herramienta Informes: Datos tabulares, suma/porcentaje, filtros básicos
- Botiquín: Elementos por instalación, cantidades exactas, solicitud manual

**PRÓXIMO PASO**: Cambiar a modo Executor para iniciar Fase 1.1

## Executor's Feedback or Assistance Requests

**ESPERANDO CONFIRMACIÓN DEL USUARIO**:
¿Proceder con Fase 1.1 (Modificar tabla instalaciones) o prefieres revisar algún aspecto del plan?

## Lessons

### Lecciones de Planificación:
1. **Análisis previo es clave**: Revisar estructura JSON existente evitó reprocessing innecesario
2. **Clarificación de requisitos**: Preguntas específicas evitan re-trabajo posterior
3. **Fases incrementales**: Dividir en fases pequeñas con criterios de éxito claros
4. **Compatibilidad histórica**: Importante mantener datos existentes al hacer cambios estructurales

### 🔧 WORKFLOW IMPORTANTE - INSTRUCCIONES DEL USUARIO:
1. **CSS**: Todo el código CSS va en `assets/css/styles.css` (mantener código limpio)
2. **Base de Datos**: Solo proporcionar SQL, el usuario crea/actualiza las tablas en servidor
3. **Archivos**: El usuario sube los archivos al servidor después de actualizarlos en local
4. **Código Limpio**: Mantener el código lo más limpio y estructurado posible