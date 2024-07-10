// Service Worker script (sw.js)

const CACHE_NAME = 'nexus-pwa-cache-v1';
const urlsToCache = [
    '/',
    '/index.php',
    '/assets/img/nexus.png',
    // tambahkan semua URL yang perlu dicache di sini
];

self.addEventListener('install', (event) => {
    // Membuat cache saat Service Worker diinstal
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', (event) => {
    // Mengembalikan konten dari cache atau jaringan
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Menggunakan respons dari cache jika ada, jika tidak, ambil dari jaringan
                return response || fetch(event.request);
            })
    );
});

// Logika lain untuk mengelola pembaruan cache jika diperlukan
