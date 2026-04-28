self.addEventListener('install', e => {
  console.log('SW Installed');
});

self.addEventListener('fetch', e => {
  // Offline support could go here
});