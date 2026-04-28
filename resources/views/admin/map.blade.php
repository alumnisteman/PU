<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISMAP | WebGIS Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white">
    <div id="map" class="h-screen w-screen"></div>
    
    <div class="absolute top-4 left-4 z-[1000] bg-slate-900/90 backdrop-blur p-4 rounded-2xl border border-slate-700 shadow-2xl">
        <div class="flex items-center gap-3 mb-4">
            <a href="/admin" class="p-2 bg-slate-800 rounded-lg hover:bg-slate-700 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="font-black tracking-tighter uppercase">WebGIS Control</h1>
        </div>
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Baik
                <span class="w-2 h-2 bg-amber-500 rounded-full ml-2"></span> Sedang
                <span class="w-2 h-2 bg-rose-500 rounded-full ml-2"></span> Rusak
            </div>
        </div>
    </div>

    <script>
        const map = L.map('map').setView([-0.65, 127.48], 10);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        fetch('/api/roads')
            .then(res => res.json())
            .then(data => {
                data.forEach(road => {
                    if (road.geometry) {
                        const color = road.condition === 'baik' ? '#10b981' : (road.condition === 'sedang' ? '#f59e0b' : '#e11d48');
                        L.geoJSON(road.geometry, {
                            style: { color: color, weight: 5, opacity: 0.8 }
                        }).bindPopup(`
                            <div class="p-2 text-slate-900 font-sans">
                                <h3 class="font-black uppercase tracking-tighter text-lg mb-1">${road.name}</h3>
                                <p class="text-xs text-slate-500 mb-2 font-mono">${road.code}</p>
                                <div class="flex gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-black uppercase">${road.condition}</span>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-black">KM: ${road.length_km}</span>
                                </div>
                                ${road.damage_type ? `
                                    <div class="mt-2 pt-2 border-t border-slate-100">
                                        <p class="text-[10px] font-black text-red-600 uppercase mb-1">Detected Damage:</p>
                                        <p class="text-xs font-bold text-slate-700">${road.damage_type}</p>
                                    </div>
                                ` : ''}
                                <a href="/admin/jalan/${road.id}/edit" class="block mt-4 text-center py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-all">Edit Data</a>
                            </div>
                        `).addTo(map);
                    }
                });
            });
    </script>
</body>
</html>
