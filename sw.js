const CACHE_NAME = 'resq-v3.2-login-fix'; // Arreglo para login
const urlsToCache = [
  '/assets/images/logo.png',
  '/assets/images/logo-negativo-soco.png',
  '/manifest.json'
  // Eliminamos CSS para forzar siempre la versión más reciente
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
  console.log('SW Install - Versión:', CACHE_NAME);
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache abierto:', CACHE_NAME);
        return cache.addAll(urlsToCache);
      })
  );
  // Forzar activación inmediata del nuevo SW
  self.skipWaiting();
});

// Interceptar peticiones de red - NETWORK FIRST para todo
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // NUNCA interceptar estas rutas - dejar que vayan directo al servidor
  if (url.pathname.startsWith('/api/') || 
      url.pathname === '/login' || 
      url.pathname === '/logout' || 
      url.pathname === '/admin/login' || 
      url.pathname === '/admin/logout' ||
      url.pathname.startsWith('/admin/api/') ||
      event.request.method === 'POST') {
    return;
  }
  
  // NETWORK FIRST para TODO - Siempre intentar red primero
  event.respondWith(
    fetch(event.request).then((response) => {
      // Si la respuesta es exitosa, devolverla directamente
      if (response && response.status === 200) {
        // Solo cachear imágenes y manifest, no PHP/CSS/JS
        if (url.pathname.includes('.png') || url.pathname.includes('.jpg') || 
            url.pathname.includes('.jpeg') || url.pathname.includes('.svg') ||
            url.pathname.includes('manifest.json')) {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      }
      
      // Para redirects (301, 302), también devolverlos directamente
      if (response && (response.status === 301 || response.status === 302)) {
        return response;
      }
      
      throw new Error('Network response was not ok');
    }).catch((error) => {
      console.log('Fetch failed for:', event.request.url, error);
      
      // Solo usar cache como fallback para recursos estáticos
      if (url.pathname.includes('.png') || url.pathname.includes('.jpg') || 
          url.pathname.includes('.jpeg') || url.pathname.includes('.svg') ||
          url.pathname.includes('manifest.json')) {
        return caches.match(event.request);
      }
      
      // Para PHP, CSS, JS y otros: mostrar error de conexión
      return new Response(
        '<html><body><h1>Sin conexión</h1><p>Inténtalo de nuevo cuando tengas conexión.</p></body></html>',
        { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
      );
    })
  );
});

// Actualizar Service Worker
self.addEventListener('activate', (event) => {
  console.log('SW Activate - Versión:', CACHE_NAME);
  const cacheWhitelist = [CACHE_NAME];
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      console.log('Caches existentes:', cacheNames);
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            console.log('Eliminando cache antiguo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      // Tomar control inmediato de todas las páginas
      return self.clients.claim();
    })
  );
});

// Manejar notificaciones push (para futuras mejoras)
self.addEventListener('push', (event) => {
  console.log('Push recibido');
  
  const options = {
    body: event.data ? event.data.text() : 'Nueva notificación de ResQ',
    icon: '/assets/images/logo.png',
    badge: '/assets/images/logo.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: '1'
    }
  };

  event.waitUntil(
    self.registration.showNotification('ResQ', options)
  );
});

// Manejar clicks en notificaciones
self.addEventListener('notificationclick', (event) => {
  console.log('Click en notificación');
  event.notification.close();

  event.waitUntil(
    clients.openWindow('/')
  );
});

// Manejar mensajes del cliente
self.addEventListener('message', (event) => {
  if (event.data && event.data.action === 'skipWaiting') {
    console.log('Recibido skipWaiting del cliente');
    self.skipWaiting();
  }
}); 