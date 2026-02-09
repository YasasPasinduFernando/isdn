// const CACHE_NAME = 'isdn-cache-v1';
// const URLS = [
//   '',
//   'index.php',
//   'assets/css/custom.css',
//   'assets/js/main.js',
//   'assets/js/validation.js',
//   'manifest.json',
//   'assets/images/icons/icon-192.svg',
//   'assets/images/icons/icon-512.svg'
// ];

// self.addEventListener('install', event => {
//   event.waitUntil(
//     caches.open(CACHE_NAME).then(cache => {
//       const absoluteUrls = URLS.map(p => new URL(p, self.registration.scope).toString());
//       return cache.addAll(absoluteUrls);
//     })
//   );
//   self.skipWaiting();
// });

// self.addEventListener('activate', event => {
//   event.waitUntil(
//     caches.keys().then(keys => Promise.all(
//       keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
//     ))
//   );
//   self.clients.claim();
// });

// self.addEventListener('fetch', event => {
//   if (event.request.method !== 'GET') return;
//   event.respondWith(
//     caches.match(event.request).then(cached => {
//       if (cached) return cached;
//       return fetch(event.request).then(resp => {
//         if (!resp || resp.status !== 200 || resp.type !== 'basic') return resp;
//         const respClone = resp.clone();
//         caches.open(CACHE_NAME).then(cache => cache.put(event.request, respClone));
//         return resp;
//       }).catch(() => caches.match('index.php'));
//     })
//   );
// });
