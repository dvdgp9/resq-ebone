# 🛡️ ResQ - Sistema de Gestión para Socorristas

Sistema web completo para la gestión de socorristas, instalaciones y formularios de incidencias.

## 📋 Características

### 🔐 Sistema de Autenticación
- **Socorristas**: Login con DNI únicamente (máxima simplicidad)
- **Administradores**: Panel de administración completo

### 📝 Formularios Principales
- **Control de Flujo de Personas**: Registro de movimiento de usuarios
- **Incidencias**: Reporte y seguimiento de incidencias
- **Parte de Accidente**: Documentación de accidentes

### 👥 Panel de Administración
- **Gestión de Coordinadores**: CRUD completo con vista de instalaciones
- **Gestión de Instalaciones**: Asignación y control
- **Gestión de Socorristas**: Administración de usuarios
- **Dashboard**: Estadísticas y navegación centralizada

### 📧 Sistema de Notificaciones
- **Email automático** a coordinadores tras completar formularios
- **Configuración SMTP** con PHPMailer
- **Fallback** a email simple si PHPMailer no está disponible

## 🚀 Tecnologías

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Email**: PHPMailer
- **Arquitectura**: MVC simplificado

## 📁 Estructura del Proyecto

```
resq-ebone/
├── index.php                 # Router principal
├── .htaccess                 # Configuración Apache
├── config/
│   ├── database.example.php  # Template de configuración BD
│   └── app.php               # Configuración general
├── classes/
│   ├── AuthService.php       # Autenticación socorristas
│   ├── AdminAuthService.php  # Autenticación admin
│   ├── AdminService.php      # Lógica CRUD admin
│   └── EmailService.php      # Servicio de emails
├── controllers/
│   ├── admin/                # Controladores admin
│   └── *.php                 # APIs de formularios
├── views/
│   ├── admin/                # Vistas del panel admin
│   ├── formularios/          # Formularios principales
│   ├── login.php             # Login socorristas
│   └── dashboard.php         # Dashboard socorristas
├── assets/
│   └── css/styles.css        # CSS centralizado
├── database/
│   ├── create_tables.sql     # Script creación BD
│   └── admin_tables.sql      # Tablas del panel admin
└── vendor/                   # PHPMailer (manual)
```

## ⚙️ Instalación

### 1. Clonar Repositorio
```bash
git clone https://github.com/tu-usuario/resq-ebone.git
cd resq-ebone
```

### 2. Configurar Base de Datos
```bash
# Copiar template de configuración
cp config/database.example.php config/database.php

# Editar credenciales en config/database.php
# Actualizar: host, database, username, password
```

### 3. Crear Base de Datos
```sql
-- Ejecutar en MySQL
CREATE DATABASE resq_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'resq_usr'@'localhost' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON resq_app.* TO 'resq_usr'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Importar Tablas
```bash
# Ejecutar scripts SQL
mysql -u resq_usr -p resq_app < database/create_tables.sql
mysql -u resq_usr -p resq_app < database/admin_tables.sql
```

### 5. Configurar Servidor Web
- **Apache**: Asegurar que mod_rewrite está habilitado
- **Nginx**: Configurar rewrite rules apropiadas

## 🔑 Credenciales por Defecto

### Panel de Administración
- **URL**: `/admin`
- **Email**: `admin@ebone.es`
- **Password**: `admin123`

### Socorristas
- **URL**: `/login`
- **Autenticación**: DNI únicamente
- **Datos**: Configurados en tabla `socorristas`

## 🌐 URLs Principales

### Socorristas
- `/` - Login
- `/dashboard` - Dashboard principal
- `/formulario/control-flujo` - Formulario control de flujo
- `/formulario/incidencias` - Formulario incidencias
- `/formulario/parte-accidente` - Formulario parte de accidente

### Administración
- `/admin` - Login admin
- `/admin/dashboard` - Dashboard admin
- `/admin/coordinadores` - Gestión coordinadores

### APIs
- `/api/control-flujo` - API formulario control
- `/api/incidencias` - API formulario incidencias
- `/api/parte-accidente` - API formulario accidentes
- `/admin/api/coordinadores` - API CRUD coordinadores

## 🔧 Configuración Email

Editar `config/app.php` con credenciales SMTP:

```php
// Configuración SMTP
define('SMTP_HOST', 'tu-servidor-smtp');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'tu-email@dominio.com');
define('SMTP_PASSWORD', 'tu-password');
define('SMTP_ENCRYPTION', 'ssl');
```

## 🛠️ Desarrollo

### Arquitectura
- **Máxima simplicidad**: Código limpio y mantenible
- **CSS centralizado**: Un solo archivo de estilos
- **APIs REST**: Respuestas JSON consistentes
- **Validación**: Frontend y backend
- **Responsive**: Diseño adaptativo

### Convenciones
- **Naming**: snake_case para archivos, camelCase para variables
- **Logging**: Todas las acciones importantes se registran
- **Seguridad**: Validación de entrada, sesiones seguras
- **Performance**: Cache inteligente, consultas optimizadas

## 📊 Base de Datos

### Relaciones Principales
```
coordinadores (1:N) instalaciones (1:N) socorristas (1:N) formularios
                                                    (1:N) sesiones
```

### Tablas Principales
- `coordinadores` - Gestores de instalaciones
- `instalaciones` - Centros de trabajo
- `socorristas` - Usuarios del sistema
- `formularios` - Datos de formularios enviados
- `sesiones` - Control de sesiones activas
- `admins` - Usuarios del panel admin
- `admin_sesiones` - Sesiones del panel admin

## 🚀 Estado del Proyecto

### ✅ Completado
- [x] Sistema de autenticación (socorristas y admin)
- [x] Los 3 formularios principales funcionando
- [x] Sistema de notificaciones por email
- [x] Panel de administración base
- [x] CRUD completo de coordinadores
- [x] Dashboard con estadísticas
- [x] Diseño responsive moderno

### 🔄 En Desarrollo
- [ ] CRUD de instalaciones
- [ ] CRUD de socorristas
- [ ] Reportes y estadísticas avanzadas

## 📝 Licencia

Proyecto privado - Todos los derechos reservados

## 👥 Contribución

Proyecto interno - Contactar con el administrador para contribuciones

---

**Desarrollado con ❤️ para mejorar la gestión de socorristas** 