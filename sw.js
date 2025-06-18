const CACHE_NAME = 'resq-v1.0.1';
const urlsToCache = [
  '/',
  '/dashboard',
  '/assets/css/styles.css',
  '/assets/images/logo.png',
  '/manifest.json'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache abierto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Interceptar peticiones de red - Estrategia Network First para formularios
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Para formularios: siempre intentar red primero
  if (url.pathname.startsWith('/formulario/') || url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(event.request).then((response) => {
        // Si la respuesta es exitosa, devolverla directamente
        if (response && response.status === 200) {
          return response;
        }
        throw new Error('Network response was not ok');
      }).catch((error) => {
        console.log('Fetch failed for:', event.request.url, error);
        // Para formularios, no usar caché - mostrar error
        return new Response(
          JSON.stringify({ error: 'No hay conexión disponible' }), 
          { status: 503, headers: { 'Content-Type': 'application/json' } }
        );
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
  const cacheWhitelist = [CACHE_NAME];
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
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