self.addEventListener("install", function (e) {
    console.log("Service Worker: Installed");
    e.waitUntil(
        caches.open("pwa-cache").then(function (cache) {
            // return cache.addAll([
            //     "/",             // Must be available online
            //     "/offline",      // Optional, only if you made this page
            //     "/css/app.css",  // Must be valid paths
            //     "/js/app.js"
            // ]).catch((err) => {
            //     console.warn("Caching failed:", err);
            // });
        })
    );
});

// self.addEventListener("fetch", function (event) {
//     if (!event.request.url.startsWith("http")) return;

//     event.respondWith(
//         // caches.match(event.request).then(function (response) {
//         //     return response || fetch(event.request);
//         // })
//     );
// });