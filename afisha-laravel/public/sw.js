const CACHE_ASSETS = 'afisha-laravel-assets-v2';
const CACHE_IMAGES = 'afisha-laravel-images-v1';

self.addEventListener('install', e => {
  e.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys.filter(k => k !== CACHE_ASSETS && k !== CACHE_IMAGES).map(k => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  if (e.request.method !== 'GET') return;

  // API — always network only
  if (url.pathname.includes('/api/')) return;

  // CSS and JS — network first, cache fallback
  if (url.pathname.endsWith('.css') || url.pathname.endsWith('.js')) {
    e.respondWith(
      fetch(e.request)
        .then(resp => {
          if (!resp || resp.status !== 200) return resp;
          const clone = resp.clone();
          caches.open(CACHE_ASSETS).then(c => c.put(e.request, clone));
          return resp;
        })
        .catch(() => caches.match(e.request))
    );
    return;
  }

  // Images — cache first
  if (/\.(png|jpe?g|gif|webp|svg|ico)(\?.*)?$/.test(url.pathname)) {
    e.respondWith(
      caches.match(e.request).then(cached => {
        if (cached) return cached;
        return fetch(e.request).then(resp => {
          if (!resp || resp.status !== 200 || resp.type === 'opaque') return resp;
          const clone = resp.clone();
          caches.open(CACHE_IMAGES).then(c => c.put(e.request, clone));
          return resp;
        });
      })
    );
    return;
  }

  // HTML — network only (always fresh Laravel pages)
});
