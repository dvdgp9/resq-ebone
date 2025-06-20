const CACHE_NAME = 'resq-v1.3.0'; // Logos optimizados: login (fondo blanco) usa logo.png, dashboard (fondo naranja) usa logo-negativo-soco.png
const urlsToCache = [
  '/assets/css/styles.css',
  '/assets/images/logo.png',
  '/assets/images/logo-negativo-soco.png',
  '/manifest.json'
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

// Interceptar peticiones de red - Estrategia mejorada
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Para formularios y API: siempre intentar red primero
  if (url.pathname.startsWith('/formulario/') || 
      url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(event.request).then((response) => {
        // Si la respuesta es exitosa, devolverla directamente
        if (response && response.status === 200) {
          return response;
        }
        // Para redirects (301, 302), también devolverlos
        if (response && (response.status === 301 || response.status === 302)) {
          return response;
        }
        throw new Error('Network response was not ok');
              }).catch((error) => {
          console.log('Fetch failed for:', event.request.url, error);
          // Para formularios y API, mostrar error
          return new Response(
            JSON.stringify({ error: 'No hay conexión disponible' }), 
            { status: 503, headers: { 'Content-Type': 'application/json' } }
          );
        })
    );
    return;
  }
  
  // Para CSS y JS: Network First con fallback a cache
  if (url.pathname.includes('.css') || url.pathname.includes('.js')) {
    event.respondWith(
      fetch(event.request).then((response) => {
        if (response && response.status === 200) {
          // Actualizar cache con nueva versión
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
          return response;
        }
        throw new Error('Network response was not ok');
      }).catch(() => {
        // Si falla la red, usar cache como fallback
        return caches.match(event.request);
      })
    );
    return;
  }
  
  // Para el resto: Cache First
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Devolver respuesta en caché si existe
        if (response) {
          return response;
        }

        return fetch(event.request).then((response) => {
          // Verificar si recibimos una respuesta válida
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // IMPORTANTE: Clonar la respuesta ya que es un stream
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then((cache) => {
              cache.put(event.request, responseToCache);
            });

          return response;
        }).catch((error) => {
          console.log('Fetch failed for:', event.request.url, error);
          throw error;
        });
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