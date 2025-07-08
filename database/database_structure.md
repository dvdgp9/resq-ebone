# Base de Datos ResQ - Estructura Completa

**Fecha de actualización:** 2025-01-12  
**Base de datos:** `resq_app`  
**Servidor:** localhost:3306  

## Resumen de Tablas

La base de datos contiene **11 tablas principales** organizadas en los siguientes módulos:

- **Administración**: `admins`, `admin_sesiones`, `admin_coordinadores`
- **Gestión de Personal**: `coordinadores`, `socorristas`
- **Instalaciones**: `instalaciones`
- **Botiquín**: `inventario_botiquin`, `historial_botiquin`, `solicitudes_material`
- **Sistema**: `sesiones`, `formularios`

---

## 1. Tabla: `admins`

**Propósito:** Almacena los administradores del sistema

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `email` | VARCHAR | Email del administrador | admin@ebone.es |
| `password_hash` | VARCHAR | Hash de la contraseña | $2y$10$4P2ahiMB7tzXYazzTN/WeTgdcZfmRhDxZd/ROpYcG... |
| `nombre` | VARCHAR | Nombre del administrador | Administrador ResQ |
| `tipo` | VARCHAR | Tipo de admin (superadmin/admin) | superadmin |
| `coordinador_id` | INT (FK) | ID del coordinador asignado (NULL = superadmin) | NULL |
| `activo` | TINYINT | Estado activo (1=activo, 0=inactivo) | 1 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-12 10:31:30 |
| `fecha_actualizacion` | TIMESTAMP | Fecha de última actualización | 2025-06-12 10:47:43 |

**Registro actual:** 1 administrador superadmin

---

## 2. Tabla: `admin_sesiones`

**Propósito:** Gestiona las sesiones de los administradores

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | VARCHAR (PK) | Token único de sesión | 0be077caf073bb5692e0abd4ed4f55ce76875bc15ea30a89fd... |
| `admin_id` | INT (FK) | ID del administrador | 1 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación de la sesión | 2025-07-04 11:06:22 |
| `fecha_expiracion` | TIMESTAMP | Fecha de expiración | 2025-07-04 15:06:22 |
| `activa` | TINYINT | Estado de la sesión (1=activa, 0=inactiva) | 1 |

**Registros actuales:** 24 sesiones (algunas activas, otras expiradas)

---

## 3. Tabla: `admin_coordinadores`

**Propósito:** Tabla intermedia para relación admin-coordinador (muchos a muchos)

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | - |
| `admin_id` | INT (FK) | ID del administrador | - |
| `coordinador_id` | INT (FK) | ID del coordinador | - |
| `fecha_asignacion` | TIMESTAMP | Fecha de asignación | - |
| `activo` | TINYINT | Estado de la relación | - |

**Estado:** Tabla vacía (0 registros)

---

## 4. Tabla: `coordinadores`

**Propósito:** Almacena los coordinadores de instalaciones

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `nombre` | VARCHAR | Nombre del coordinador | David Guti |
| `email` | VARCHAR | Email del coordinador | dgutierrez@ebone.es |
| `telefono` | VARCHAR | Teléfono de contacto | 666123456 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-10 15:49:57 |
| `fecha_actualizacion` | TIMESTAMP | Fecha de última actualización | 2025-06-11 12:15:46 |

**Registro actual:** 1 coordinador

---

## 5. Tabla: `socorristas`

**Propósito:** Almacena los socorristas del sistema

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `dni` | VARCHAR | DNI del socorrista | 12345678Z |
| `nombre` | VARCHAR | Nombre completo | María García Pérez |
| `email` | VARCHAR | Email del socorrista | maria.garcia@ebone.es |
| `telefono` | VARCHAR | Teléfono de contacto | NULL |
| `instalacion_id` | INT (FK) | ID de la instalación asignada | 4 |
| `activo` | TINYINT | Estado activo (1=activo, 0=inactivo) | 1 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-10 15:49:57 |
| `fecha_actualizacion` | TIMESTAMP | Fecha de última actualización | 2025-06-19 11:55:06 |

**Registros actuales:** 2 socorristas (María García Pérez y Carlos López Rescate)

---

## 6. Tabla: `instalaciones`

**Propósito:** Almacena las instalaciones acuáticas

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `nombre` | VARCHAR | Nombre de la instalación | Sede Central |
| `direccion` | VARCHAR | Dirección física | Calle Principal 123, 28001 Madrid |
| `coordinador_id` | INT (FK) | ID del coordinador asignado | 1 |
| `activo` | TINYINT | Estado activo (1=activo, 0=inactivo) | 1 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-10 15:49:57 |
| `fecha_actualizacion` | TIMESTAMP | Fecha de última actualización | 2025-06-10 15:49:57 |
| `espacios_personalizables` | TEXT | Configuración de espacios | NULL |
| `aforo_maximo` | INT | Aforo máximo de la instalación | NULL |

**Registros actuales:** 4 instalaciones (Sede Central, Sucursal Barcelona, Instalación 3, Piscina Test Espacios)

---

## 7. Tabla: `inventario_botiquin`

**Propósito:** Inventario actual del botiquín por instalación

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `instalacion_id` | INT (FK) | ID de la instalación | 4 |
| `nombre_elemento` | VARCHAR | Nombre del producto | Aspirinas 500mg |
| `categoria` | ENUM | Categoría del producto | medicamentos |
| `cantidad_actual` | INT | Cantidad disponible | 8 |
| `unidad_medida` | VARCHAR | Unidad de medida | Pastillas |
| `observaciones` | TEXT | Observaciones adicionales | - |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-19 17:37:19 |
| `fecha_ultima_actualizacion` | TIMESTAMP | Fecha de última actualización | 2025-07-01 13:18:13 |
| `socorrista_ultima_actualizacion` | INT (FK) | ID del socorrista que actualizó | 1 |
| `activo` | TINYINT | Estado activo (1=activo, 0=inactivo) | 1 |

**Registros actuales:** 15 productos activos en diferentes categorías (medicamentos, general, instrumental, otros)

**Categorías disponibles:** `general`, `medicamentos`, `instrumental`, `otros`

---

## 8. Tabla: `historial_botiquin`

**Propósito:** Historial de cambios en el botiquín

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `inventario_id` | INT (FK) | ID del elemento del inventario | 1 |
| `socorrista_id` | INT (FK) | ID del socorrista que hizo el cambio | 1 |
| `accion` | ENUM | Tipo de acción realizada | creado |
| `cantidad_anterior` | INT | Cantidad antes del cambio | NULL |
| `cantidad_nueva` | INT | Cantidad después del cambio | 10 |
| `observaciones` | TEXT | Observaciones del cambio | Elemento creado: Aspirinas 500mg |
| `fecha_accion` | TIMESTAMP | Fecha y hora de la acción | 2025-06-19 17:37:19 |

**Registros actuales:** 24 registros de historial

**Acciones disponibles:** `creado`, `actualizado`

---

## 9. Tabla: `solicitudes_material`

**Propósito:** Solicitudes de material de socorristas a coordinadores

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `instalacion_id` | INT (FK) | ID de la instalación | 4 |
| `socorrista_id` | INT (FK) | ID del socorrista solicitante | 1 |
| `elementos_solicitados` | JSON | Elementos solicitados en formato JSON | [{"nombre":"Vendas","cantidad":10,"observaciones":"..."}] |
| `mensaje_adicional` | TEXT | Mensaje adicional del socorrista | - |
| `estado` | ENUM | Estado de la solicitud | pendiente |
| `fecha_solicitud` | TIMESTAMP | Fecha de la solicitud | 2025-06-30 13:11:45 |
| `fecha_envio` | TIMESTAMP | Fecha de envío | NULL |

**Registros actuales:** 5 solicitudes (4 pendientes, 1 enviada)

**Estados disponibles:** `pendiente`, `enviada`

---

## 10. Tabla: `sesiones`

**Propósito:** Gestiona las sesiones de los socorristas

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | VARCHAR (PK) | Token único de sesión | 00446efe6d4a6ce943066814fd6dd590da039b6d4185d515fd... |
| `socorrista_id` | INT (FK) | ID del socorrista | 1 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-18 10:56:55 |
| `fecha_expiracion` | TIMESTAMP | Fecha de expiración | 2025-06-18 11:56:55 |
| `activa` | TINYINT | Estado de la sesión (1=activa, 0=inactiva) | 1 |

**Registros actuales:** 24 sesiones (algunas activas, otras expiradas)

---

## 11. Tabla: `formularios`

**Propósito:** Almacena los formularios del sistema (control de flujo, incidencias)

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT (AI, PK) | Identificador único | 1 |
| `socorrista_id` | INT (FK) | ID del socorrista | 1 |
| `tipo_formulario` | ENUM | Tipo de formulario | control_flujo |
| `datos_json` | JSON | Datos del formulario en formato JSON | {"fecha_hora":"2025-06-11T16:31","tipo_movimiento":"..."} |
| `notificacion_enviada` | TINYINT | Si se envió notificación (1=sí, 0=no) | 0 |
| `fecha_creacion` | TIMESTAMP | Fecha de creación | 2025-06-11 11:32:43 |

**Registros actuales:** 12 formularios (10 control_flujo, 2 incidencias)

**Tipos disponibles:** `control_flujo`, `incidencias`

---

## Relaciones entre Tablas

### Relaciones Principales:

1. **`admins` → `coordinadores`** (1:1 opcional)
   - `admins.coordinador_id` → `coordinadores.id`
   - Permite asignar un coordinador específico a un admin

2. **`admin_coordinadores`** (Tabla intermedia muchos a muchos)
   - `admin_coordinadores.admin_id` → `admins.id`
   - `admin_coordinadores.coordinador_id` → `coordinadores.id`

3. **`coordinadores` → `instalaciones`** (1:N)
   - `instalaciones.coordinador_id` → `coordinadores.id`
   - Un coordinador puede tener múltiples instalaciones

4. **`instalaciones` → `socorristas`** (1:N)
   - `socorristas.instalacion_id` → `instalaciones.id`
   - Una instalación puede tener múltiples socorristas

5. **`instalaciones` → `inventario_botiquin`** (1:N)
   - `inventario_botiquin.instalacion_id` → `instalaciones.id`
   - Cada instalación tiene su propio inventario

6. **`inventario_botiquin` → `historial_botiquin`** (1:N)
   - `historial_botiquin.inventario_id` → `inventario_botiquin.id`
   - Cada elemento del inventario puede tener múltiples cambios

7. **`socorristas` → `historial_botiquin`** (1:N)
   - `historial_botiquin.socorrista_id` → `socorristas.id`
   - Rastrea qué socorrista hizo cada cambio

8. **`instalaciones` → `solicitudes_material`** (1:N)
   - `solicitudes_material.instalacion_id` → `instalaciones.id`
   - Cada instalación puede tener múltiples solicitudes

9. **`socorristas` → `solicitudes_material`** (1:N)
   - `solicitudes_material.socorrista_id` → `socorristas.id`
   - Cada socorrista puede hacer múltiples solicitudes

10. **`socorristas` → `sesiones`** (1:N)
    - `sesiones.socorrista_id` → `socorristas.id`
    - Cada socorrista puede tener múltiples sesiones

11. **`admins` → `admin_sesiones`** (1:N)
    - `admin_sesiones.admin_id` → `admins.id`
    - Cada admin puede tener múltiples sesiones

12. **`socorristas` → `formularios`** (1:N)
    - `formularios.socorrista_id` → `socorristas.id`
    - Cada socorrista puede crear múltiples formularios

---

## Análisis de Datos Actuales

### Resumen de Registros:
- **Administradores:** 1 superadmin
- **Coordinadores:** 1 coordinador activo
- **Socorristas:** 2 socorristas activos
- **Instalaciones:** 4 instalaciones (3 operativas + 1 test)
- **Inventario Botiquín:** 15 productos activos
- **Historial Botiquín:** 24 cambios registrados
- **Solicitudes Material:** 5 solicitudes (4 pendientes)
- **Sesiones Activas:** Múltiples sesiones de usuarios y admins
- **Formularios:** 12 formularios (10 control_flujo, 2 incidencias)

### Estado del Sistema:
- ✅ **Sistema de autenticación dual** (socorristas + admins)
- ✅ **Gestión de instalaciones** operativa
- ✅ **Sistema de botiquín** funcional
- ✅ **Seguimiento de inventario** con historial
- ✅ **Sistema de solicitudes** implementado
- ✅ **Formularios digitales** (control flujo + incidencias)
- ⚠️ **Tabla intermedia admin_coordinadores** vacía (no utilizada)

### Observaciones Técnicas:
- **Encoding:** UTF-8 con soporte completo para caracteres especiales
- **Timestamps:** Formato YYYY-MM-DD HH:MM:SS
- **JSON:** Utilizado para datos estructurados (formularios, solicitudes)
- **Seguridad:** Hashes de contraseñas con bcrypt
- **Sesiones:** Tokens únicos con expiración automática

---

**Documento generado:** 2025-07
**Autor:** Sistema ResQ - Análisis de BD  
**Versión:** 1.0