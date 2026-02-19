/*
 * ISDN Service Worker (subfolder-safe for /isdn)
 * - Versioned cache name
 * - Network-first for HTML/documents
 * - Cache-first for static assets
 * - Offline fallback to /isdn/index.php
 * - Old cache cleanup on activate
 */

const APP_SCOPE = '/isdn/';
const OFFLINE_FALLBACK = '/isdn/index.php';
const CACHE_NAME = 'isdn-cache-v3';

const STATIC_ASSETS = [
  '/isdn/',
  '/isdn/index.php',
  '/isdn/manifest.json',
  '/isdn/assets/css/style.css',
  '/isdn/assets/css/custom.css',
  '/isdn/assets/js/main.js',
  '/isdn/assets/js/validation.js',
  '/isdn/assets/images/icons/icon-192.png',
  '/isdn/assets/images/icons/icon-512.png'
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
  if (!reqUrl.pathname.startsWith(APP_SCOPE)) return;

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
