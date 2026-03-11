const STATIC_CACHE = 'ventas-static-v2';
const DYNAMIC_CACHE = 'ventas-dynamic-v1';
const IMMUTABLE_CACHE = 'ventas-immutable-v1';

const APP_SHELL = [
  '/',
  '/index.php',
  '/manifest.json',
  '/offline.html',
  '/app/views/css/bulma.min.css',
  '/app/views/css/estilos.css',
  '/app/views/css/all.css',
  '/app/views/js/main.js',
  '/app/views/js/ajax.js',
  '/app/views/img/logo.png'
];

function limitCacheSize(cacheName, maxItems){
  caches.open(cacheName).then(cache => {
    cache.keys().then(keys => {
      if (keys.length > maxItems) cache.delete(keys[0]).then(() => limitCacheSize(cacheName, maxItems));
    });
  });
}

self.addEventListener('install', e => {
  e.waitUntil((async () => {
    const cacheStatic = await caches.open(STATIC_CACHE);
    try {
      await cacheStatic.addAll(APP_SHELL);
    } catch (err) {
      console.warn('cache.addAll failed, falling back to individual fetches', err);
      for (const asset of APP_SHELL) {
        try {
          const res = await fetch(asset, { cache: 'no-cache' });
          if (res && res.ok) await cacheStatic.put(asset, res.clone());
        } catch (err2) {
          console.warn('Failed to fetch/cache asset', asset, err2);
        }
      }
    }

    const cacheImmutable = await caches.open(IMMUTABLE_CACHE);
    // Add CDN or versioned third-party assets here if any
    return;
  })());
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.map(key => {
        if (key !== STATIC_CACHE && key !== DYNAMIC_CACHE && key !== IMMUTABLE_CACHE) return caches.delete(key);
      })
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', e => {
  const req = e.request;
  const url = new URL(req.url);

  // Don't attempt to cache non-GET requests (forms, API POSTs, etc.).
  // For navigations that use POST, just forward to network and
  // fall back to offline page on failure.
  if (req.method !== 'GET'){
    e.respondWith(
      (async () => {
        try {
          return await fetch(req);
        } catch (err) {
          if (req.mode === 'navigate') {
            const cached = await caches.match('/offline.html');
            return cached || new Response('Offline', { status: 503, statusText: 'Offline' });
          }
          return new Response('Network error', { status: 408, statusText: 'Network Error' });
        }
      })()
    );
    return;
  }

  // Navigation requests: network-first, fallback to cache/offline
  if (req.mode === 'navigate'){
    e.respondWith(
      (async () => {
        try {
          const res = await fetch(req);
          if (res && res.ok) {
            const resForCache = res.clone();
            caches.open(DYNAMIC_CACHE).then(cache => cache.put(req, resForCache).catch(err => console.warn('dynamic cache put failed', err)));
          }
          limitCacheSize(DYNAMIC_CACHE, 50);
          return res;
        } catch (err) {
          const cached = await caches.match('/offline.html');
          return cached || new Response('Offline', { status: 503, statusText: 'Offline' });
        }
      })()
    );
    return;
  }

  // Static assets: cache-first
  if (APP_SHELL.some(asset => url.pathname.endsWith(asset.replace('/', '')))){
    e.respondWith((async () => {
      const cached = await caches.match(req);
      if (cached) return cached;
      try {
        const res = await fetch(req);
        if (res && res.ok) {
          const resForCache = res.clone();
          caches.open(STATIC_CACHE).then(cache => cache.put(req, resForCache).catch(err => console.warn('static cache put failed', err)));
        }
        return res;
      } catch (err) {
        const fallback = await caches.match('/offline.html');
        return fallback || new Response('Offline', { status: 503, statusText: 'Offline' });
      }
    })());
    return;
  }

  // Fallback: try cache, else network then cache dynamic
    e.respondWith((async () => {
      try {
        const cached = await caches.match(req);
        if (cached) return cached;
        const res = await fetch(req);
        if (res && res.ok) {
          const resForCache = res.clone();
          await caches.open(DYNAMIC_CACHE).then(cache => cache.put(req, resForCache).catch(err => console.warn('dynamic cache put failed', err)));
          limitCacheSize(DYNAMIC_CACHE, 50);
        }
        return res;
      } catch (err) {
        if (req.destination === 'image') {
          const imgCached = await caches.match('/app/views/img/logo.png');
          return imgCached || new Response('', { status: 404 });
        }
        const offline = await caches.match('/offline.html');
        return offline || new Response('Offline', { status: 503, statusText: 'Offline' });
      }
    })());
});

self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

