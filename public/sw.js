// Service Worker Killer - Force unregister and clear all caches
self.addEventListener('install', function(e) {
    self.skipWaiting();
});

self.addEventListener('activate', function(e) {
    self.registration.unregister()
        .then(function() {
            return self.clients.matchAll();
        })
        .then(function(clients) {
            clients.forEach(client => client.navigate(client.url));
        });
});

// Clear all caches
caches.keys().then(function(names) {
    for (let name of names) caches.delete(name);
});