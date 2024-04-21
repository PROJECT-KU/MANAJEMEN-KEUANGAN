const filesToCache = [
    '/',
    '/offline.html'
];

const preLoad = function () {
    return caches.open("offline").then(function (cache) {
        // Menyimpan file-file yang perlu di-cache
        return cache.addAll(filesToCache);
    });
};

self.addEventListener("install", function (event) {
    event.waitUntil(preLoad());
});

const checkResponse = function (request) {
    return new Promise(function (fulfill, reject) {
        // Memeriksa respons dari server
        fetch(request).then(function (response) {
            if (response.status !== 404) {
                fulfill(response);
            } else {
                reject();
            }
        }).catch(reject);
    });
};

const addToCache = function (request) {
    // Menambahkan permintaan ke dalam cache jika skema protokol adalah HTTP atau HTTPS
    if (request.url.startsWith('http')) {
        return caches.open("offline").then(function (cache) {
            return fetch(request).then(function (response) {
                // Menyimpan respons ke dalam cache
                return cache.put(request, response.clone());
            });
        });
    }
};


const returnFromCache = function (request) {
    return caches.open("offline").then(function (cache) {
        return cache.match(request).then(function (matching) {
            // Mengembalikan dari cache jika ada, jika tidak ada, kembalikan halaman offline
            if (!matching || matching.status === 404) {
                return cache.match("offline.html");
            } else {
                return matching;
            }
        });
    });
};

self.addEventListener("fetch", function (event) {
    event.respondWith(
        // Memeriksa respons dari server, jika gagal, kembalikan dari cache
        checkResponse(event.request).catch(function () {
            return returnFromCache(event.request);
        })
    );

    // Menambahkan permintaan ke dalam cache
    if (!event.request.url.startsWith('http')) {
        event.waitUntil(addToCache(event.request));
    }
});
