# 📱 ResQ PWA - Instalación

## ¿Qué es una PWA?
Una Progressive Web App (PWA) permite usar ResQ como si fuera una aplicación nativa en tu teléfono o tablet, con acceso directo desde la pantalla de inicio.

## 🚀 Beneficios
- **Acceso directo**: Icono en la pantalla de inicio
- **Carga rápida**: Funciona sin conexión una vez cargada
- **Experiencia nativa**: Pantalla completa sin barra del navegador
- **Notificaciones**: Preparado para recibir notificaciones push

## 📲 Cómo instalar

### En Android (Chrome)
1. Abre **Chrome** y ve a `https://resq.ebone.es`
2. En la barra de direcciones aparecerá un icono de **"Instalar"** o **"+"**
3. Toca **"Instalar ResQ"**
4. Confirma la instalación
5. ¡Ya tienes ResQ en tu pantalla de inicio!

### En iPhone/iPad (Safari)
1. Abre **Safari** y ve a `https://resq.ebone.es`
2. Toca el botón **"Compartir"** (cuadrado con flecha hacia arriba)
3. Desplázate y selecciona **"Agregar a pantalla de inicio"**
4. Cambia el nombre si quieres (por defecto será "ResQ")
5. Toca **"Agregar"**
6. ¡La app aparecerá en tu pantalla de inicio!

### En Windows/Mac (Chrome/Edge)
1. Abre el navegador y ve a `https://resq.ebone.es`
2. Haz clic en el icono de **instalación** en la barra de direcciones
3. O ve a **Menú > Instalar ResQ**
4. Confirma la instalación
5. ResQ se abrirá como aplicación independiente

## ✨ Características PWA de ResQ

### 🏠 Pantalla de inicio
- Se abre directamente en el login de socorristas
- Color de tema naranja lifeguard (#D33E22)
- Logo optimizado para móviles

### ⚡ Accesos rápidos
Mantén presionado el icono para acceder rápidamente a:
- Control de Flujo de Personas
- Reportar Incidencias  
- Parte de Accidente

### 📴 Funcionamiento offline
- Las páginas visitadas se guardan en caché
- Puedes acceder aunque no tengas internet
- Los formularios se enviarán cuando recuperes conexión

## 🔧 Características técnicas
- Service Worker registrado para funcionamiento offline
- Caché inteligente de recursos críticos
- Optimizado para dispositivos móviles
- Cumple estándares PWA de Google y Apple

## ❓ Resolución de problemas

**No aparece el botón de instalación:**
- Asegúrate de estar usando HTTPS (https://resq.ebone.es)
- Prueba refrescando la página
- Verifica que tu navegador soporte PWAs

**La app no funciona offline:**
- Espera unos segundos tras la primera carga
- Navega por las páginas principales al menos una vez

**¿Cómo desinstalar?**
- **Android**: Mantén presionado el icono > "Desinstalar"
- **iOS**: Mantén presionado el icono > "Eliminar app"
- **Desktop**: Configuración de la app > Desinstalar

## 📋 Lista de verificación para administradores

Antes de distribuir la PWA, verificar:
- [ ] El sitio funciona correctamente en HTTPS
- [ ] El logo se ve bien en diferentes tamaños (192x192, 512x512)
- [ ] Los formularios principales cargan sin problemas
- [ ] El Service Worker se registra correctamente
- [ ] La PWA aparece en el instalador del navegador
- [ ] Los shortcuts funcionan correctamente

## 🎯 Instrucciones para socorristas

### Primera instalación
1. **Instala la app** siguiendo los pasos de tu dispositivo
2. **Abre ResQ** desde la pantalla de inicio
3. **Inicia sesión** con tu DNI
4. **Navega** por las opciones principales para que se guarden en caché
5. **¡Ya está listo!** Ahora puedes usar ResQ como una app nativa

### Uso diario
- Abre ResQ desde el icono en tu pantalla de inicio
- La app cargará rápidamente incluso con conexión lenta
- Todos los formularios están disponibles desde el dashboard
- Si pierdes conexión, las páginas visitadas seguirán funcionando

### Consejos
- **Mantén la app actualizada**: Refrescar ocasionalmente cuando haya conexión
- **Reporta problemas**: Si algo no funciona, contacta con tu coordinador
- **Usa los shortcuts**: Mantén presionado el icono para acceso rápido

---

## 🔄 Actualizaciones automáticas
La PWA se actualiza automáticamente cuando:
- Se detecta una nueva versión en el servidor
- El usuario refresca la aplicación
- Se reinicia la aplicación tras un periodo inactivo

## 🛡️ Seguridad
- Todas las comunicaciones usan HTTPS
- Los datos se almacenan de forma segura
- La autenticación se mantiene entre sesiones
- El caché no guarda información sensible

---
*Desarrollado para facilitar el acceso de socorristas y personal de salvamento* 🏊‍♂️🏖️ 