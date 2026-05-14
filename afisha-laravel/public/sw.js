const CACHE_SHELL   = 'afisha-shell-v6';   // HTML + manifest
const CACHE_ASSETS  = 'afisha-assets-v6';  // CSS + JS
const CACHE_IMAGES  = 'afisha-images-v6';  // картинки

// Только HTML-оболочки кэшируем при установке
const SHELL = [
  '/frontend/index.html',
  '/frontend/manifest.json',
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_SHELL)
      .then(c => c.addAll(SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  const keep = [CACHE_SHELL, CACHE_ASSETS, CACHE_IMAGES];
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys.filter(k => !keep.includes(k)).map(k => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  if (e.request.method !== 'GET') return;

  // 1. API — всегда сеть; кэш только при офлайне
  if (url.pathname.startsWith('/afisha/')) {
    e.respondWith(
      fetch(e.request).catch(() => caches.match(e.request))
    );
    return;
  }

  // 2. CSS и JS — сеть первой, кэш при офлайне (всегда получаем свежие стили)
  if (url.pathname.endsWith('.css') || url.pathname.endsWith('.js') ||
      url.pathname.includes('.css?')  || url.pathname.includes('.js?')) {
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

  // 3. Изображения — кэш первым (меняются редко, экономим трафик)
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

  // 4. HTML и всё остальное — сеть первой, кэш при офлайне
  e.respondWith(
    fetch(e.request)
      .then(resp => {
        if (!resp || resp.status !== 200 || resp.type === 'opaque') return resp;
        const clone = resp.clone();
        caches.open(CACHE_SHELL).then(c => c.put(e.request, clone));
        return resp;
      })
      .catch(() => caches.match(e.request))
  );
});
