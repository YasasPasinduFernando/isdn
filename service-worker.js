/*
 * ISDN Service Worker (root + subfolder safe)
 * - Versioned cache name
 * - Network-first for HTML/documents
 * - Cache-first for static assets
 * - Offline fallback to index.php within current scope
 * - Old cache cleanup on activate
 */

const SCOPE_PATH = new URL(self.registration.scope).pathname.replace(/\/+$/, '/') || '/';
const OFFLINE_FALLBACK = `${SCOPE_PATH}index.php`;
const CACHE_NAME = 'isdn-cache-v7';

const STATIC_ASSETS = [
  SCOPE_PATH,
  `${SCOPE_PATH}index.php`,
  `${SCOPE_PATH}manifest.json`,
  `${SCOPE_PATH}assets/css/style.css`,
  `${SCOPE_PATH}assets/css/custom.css`,
  `${SCOPE_PATH}assets/js/main.js`,
  `${SCOPE_PATH}assets/js/validation.js`,
  `${SCOPE_PATH}assets/images/icons/icon-192.png`,
  `${SCOPE_PATH}assets/images/icons/icon-512.png`
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const reqUrl = new URL(event.request.url);
  if (reqUrl.origin !== self.location.origin) return;
  if (!reqUrl.pathname.startsWith(SCOPE_PATH)) return;

  const isDocument =
    event.request.mode === 'navigate' || event.request.destination === 'document';

  if (isDocument) {
    // Network-first for HTML to keep dynamic PHP pages fresh.
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          return response;
        })
        .catch(() =>
          caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_FALLBACK))
        )
    );
    return;
  }

  // Cache-first for static assets.
  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request).then((response) => {
        if (!response || response.status !== 200 || response.type !== 'basic') return response;
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        return response;
      });
    })
  );
});
