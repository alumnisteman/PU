<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        // Force clear Service Worker and Cache to ensure new code loads
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) { registration.unregister(); }
            });
        }
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) caches.delete(name);
            });
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISMAP Modern | Admin Infrastructure Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Map Libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Marker Cluster -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <!-- Heatmap & Pusher -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <style>
        html, body { background-color: #0f172a; color: #f8fafc; font-family: sans-serif; margin: 0; padding: 0; }
        #map { height: 500px; border-radius: 1.5rem; background: #1e293b; }
        .custom-popup .leaflet-popup-content-wrapper { background: #1e293b; color: white; border-radius: 12px; }
    </style>
</head>
<body class="bg-[#0f172a] text-slate-200 overflow-hidden">
    <div class="flex h-screen w-full">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col z-50 shadow-2xl">
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg shadow-lg shadow-blue-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tighter text-white leading-tight">SISMAP</h1>
                    <p class="text-[9px] text-blue-400 font-bold uppercase tracking-widest">Command Center</p>
                </div>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-500 rounded-xl font-bold text-sm transition-colors border border-blue-600/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Live Map
                </a>
                <a href="{{ route('jalan.create') }}" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-slate-200 hover:bg-slate-800 rounded-xl font-bold text-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Data Entry
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-slate-200 hover:bg-slate-800 rounded-xl font-bold text-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Reports
                </a>

                <!-- Clean Logout Button in Nav -->
                <div class="pt-8 mt-8 border-t border-slate-800/50">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-rose-400 hover:text-white hover:bg-rose-600 rounded-xl font-bold text-sm transition-all shadow-lg shadow-rose-600/10 group">
                            <svg class="w-5 h-5 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout System
                        </button>
                    </form>
                </div>
            </nav>
            
            <div class="p-6 border-t border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center border border-slate-700 shadow-inner">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-200">Admin Provinsi</p>
                        <p class="text-[10px] text-emerald-400 font-mono mt-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-950">
            
            <!-- Topbar Stats -->
            <header class="h-20 bg-slate-900 border-b border-slate-800 flex items-center px-6 gap-6 shrink-0 shadow-sm z-40">
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Status Jaringan</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></span>
                        <span class="text-xs font-black text-emerald-400 tracking-wider">LIVE</span>
                    </div>
                </div>
                
                <div class="h-8 w-px bg-slate-800 mx-2"></div>
                
                <div class="flex items-center gap-8 flex-1">
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Total Infrastruktur</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-black text-white" id="total_km">...</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Kondisi Baik</span>
                        <div class="text-xl font-black text-emerald-400" id="count_baik">...</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-rose-500 font-bold uppercase tracking-widest">Rusak Berat (Prioritas)</span>
                        <div class="text-xl font-black text-rose-500" id="count_rusak_berat">...</div>
                    </div>
                </div>
                
                <div id="debug-error" class="hidden px-4 py-2 bg-rose-500/10 border border-rose-500/20 rounded-lg max-w-xs overflow-hidden">
                    <p class="text-rose-500 text-[10px] font-black uppercase tracking-widest mb-1 truncate">System Error</p>
                    <ul id="error-list" class="text-[9px] text-rose-400 font-mono list-disc ml-3 truncate"></ul>
                </div>
            </header>

            <!-- Map & Right Panel Layout -->
            <div class="flex-1 flex min-h-0">
                
                <!-- Center Map -->
                <div class="flex-1 p-6 flex flex-col">
                    <div class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative">
                        <div id="map" class="absolute inset-0"></div>
                        
                        <!-- Map Floating Legend -->
                        <div class="absolute bottom-6 left-6 z-[400] bg-slate-900/90 backdrop-blur-md border border-slate-700 p-4 rounded-xl shadow-xl">
                            <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3">Legenda Peta</h3>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-300"><div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_5px_rgba(225,29,72,0.5)]"></div> Rusak Berat</div>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-300"><div class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_5px_rgba(245,158,11,0.5)]"></div> Rusak Sedang</div>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-300"><div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></div> Kondisi Baik</div>
                                <div class="h-px bg-slate-700 my-2"></div>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-blue-400"><div class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_5px_rgba(59,130,246,0.5)]"></div> Tim Lapangan (Live)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel (30%) -->
                <aside class="w-[380px] bg-slate-900 border-l border-slate-800 flex flex-col shrink-0 overflow-hidden shadow-2xl">
                    
                    <!-- Chart Section -->
                    <div class="p-6 border-b border-slate-800 shrink-0">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4 flex justify-between items-center">
                            Distribusi Kerusakan
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        </h3>
                        <div class="h-40 w-full relative">
                            <canvas id="conditionChart"></canvas>
                        </div>
                    </div>

                    <!-- Priority Roads List -->
                    <div class="flex-1 overflow-y-auto p-0 flex flex-col bg-slate-900/50">
                        <div class="p-4 bg-rose-500/5 border-b border-rose-500/10 sticky top-0 backdrop-blur-md z-10 flex justify-between items-center">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-rose-500 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                </span>
                                Top Priority Roads
                            </h3>
                            <span class="text-[9px] text-rose-400/70 font-mono font-bold bg-rose-500/10 px-2 py-0.5 rounded">AI SCORED</span>
                        </div>
                        
                        <div id="priority_list" class="flex-1 p-2 space-y-2">
                            <!-- Populated via JS -->
                            <div class="p-8 text-center text-slate-600 text-xs italic">Memuat data prioritas...</div>
                        </div>
                    </div>
                    
                </aside>
                
            </div>
        </main>
    </div>

    <script>
        function logError(msg) {
            const el = document.getElementById('debug-error');
            if(el) {
                el.classList.remove('hidden');
                const li = document.createElement('li'); li.innerText = msg;
                document.getElementById('error-list').appendChild(li);
            }
        }

        let map, markersGroup, conditionChart, heatLayer;
        let workerMarkers = {};

        function initMap() {
            try {
                // Initialize map inside the new layout
                map = L.map('map', { zoomControl: false, attributionControl: false }).setView([-0.7893, 127.3750], 12);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
                
                // Add zoom control at top right
                L.control.zoom({ position: 'topright' }).addTo(map);
                
                markersGroup = L.markerClusterGroup().addTo(map);
                heatLayer = L.heatLayer([], {radius: 25, blur: 15, maxZoom: 17}).addTo(map);
            } catch (e) { logError("Map Error: " + e.message); }
        }

        const workerIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-[0_0_10px_rgba(59,130,246,0.8)] animate-pulse"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });

        function updateWorkerMarker(worker) {
            if (!workerMarkers[worker.id]) {
                workerMarkers[worker.id] = L.marker([worker.lat, worker.lng], {icon: workerIcon})
                    .bindPopup(`<div class="font-bold text-xs text-slate-800">${worker.name}</div><div class="text-[10px] text-blue-600 font-bold uppercase">${worker.village || 'Detecting...'}</div>`)
                    .addTo(map);
            } else {
                workerMarkers[worker.id].setLatLng([worker.lat, worker.lng]);
                workerMarkers[worker.id].setPopupContent(`<div class="font-bold text-xs text-slate-800">${worker.name}</div><div class="text-[10px] text-blue-600 font-bold uppercase">${worker.village || 'Detecting...'}</div>`);
            }
        }

        function initPusher() {
            try {
                const pusher = new Pusher('key', {
                    wsHost: window.location.hostname,
                    wsPort: 6001,
                    forceTLS: false,
                    disableStats: true,
                    enabledTransports: ['ws', 'wss']
                });

                const channel = pusher.subscribe('workers');
                channel.bind('App\\Events\\WorkerLocationUpdated', function(data) {
                    updateWorkerMarker(data.worker);
                });
            } catch (e) { logError("Pusher Error: " + e.message); }
        }

        let simLat = -0.789300; // Ternate City Center
        let simLng = 127.375000;
        function startSimulation() {
            setInterval(() => {
                // Moving slowly through Ternate streets
                simLat += (Math.random() - 0.5) * 0.0005;
                simLng += (Math.random() - 0.5) * 0.0005;
                
                fetch('/api/worker/update-location', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({ 
                        id: 1, 
                        name: "Tim Reaksi Cepat (TRC)", 
                        lat: simLat, 
                        lng: simLng 
                    })
                }).catch(e => console.log('Sim error', e));
            }, 4000);
        }

        async function loadData() {
            try {
                const res = await fetch('/api/roads/dashboard?t=' + Date.now());
                const data = await res.json();
                
                // Update Topbar Stats
                if(document.getElementById('total_km')) document.getElementById('total_km').innerText = data.total_km + ' KM';
                if(document.getElementById('count_baik')) document.getElementById('count_baik').innerText = data.condition_stats.baik;
                if(document.getElementById('count_rusak_berat')) document.getElementById('count_rusak_berat').innerText = data.condition_stats.rusak_berat;

                // Update Chart in Right Panel
                const ctxEl = document.getElementById('conditionChart');
                if(ctxEl) {
                    const ctx = ctxEl.getContext('2d');
                    if (conditionChart) conditionChart.destroy();
                    conditionChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'],
                            datasets: [{
                                data: [data.condition_stats.baik, data.condition_stats.sedang, data.condition_stats.rusak_ringan, data.condition_stats.rusak_berat],
                                backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#e11d48'], 
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: { 
                            cutout: '80%', 
                            responsive: true, 
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { display: false } 
                            } 
                        }
                    });
                }

                // Update Priority List in Right Panel
                const listEl = document.getElementById('priority_list');
                if(listEl) {
                    listEl.innerHTML = '';
                    if(data.priority_roads.length === 0) {
                        listEl.innerHTML = '<div class="p-8 text-center text-slate-500 text-xs italic">Semua infrastruktur dalam kondisi baik.</div>';
                    }

                    // Heatmap data
                    if(heatLayer) {
                        const heatPoints = data.priority_roads
                            .filter(r => r.condition === 'rusak_berat' && r.lat && r.lng)
                            .map(r => [r.lat, r.lng, 1.0]);
                        heatLayer.setLatLngs(heatPoints);
                    }
                    data.priority_roads.slice(0, 15).forEach((road, index) => {
                        const budgetJuta = (road.estimated_budget / 1000000).toFixed(1);
                        const isCritical = road.condition === 'rusak_berat';
                        
                        // Focus Map on click
                        const clickHandler = `if(window.parent && window.parent.map) { window.parent.map.setView([${road.lat || -0.7893}, ${road.lng || 127.3750}], 17, {animate:true}); }`;
                        
                        listEl.innerHTML += `
                            <div class="group bg-slate-800/40 hover:bg-slate-800 border border-slate-700 hover:border-slate-600 p-3 rounded-xl cursor-pointer transition-all ${isCritical ? 'border-rose-500/30 bg-rose-500/5' : ''}" onclick="${clickHandler}">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-sm text-slate-200 group-hover:text-blue-400 transition-colors">${road.name}</div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5 flex items-center gap-2">
                                            <span>${road.code}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                            <span class="${isCritical ? 'text-rose-400' : 'text-amber-400'} uppercase tracking-wider">${road.condition.replace('_', ' ')}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[22px] font-black ${isCritical ? 'text-rose-500' : 'text-blue-400'} leading-none">${road.priority_score}</div>
                                        <div class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-1">SCORE</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="text-slate-400 font-medium bg-slate-900 px-2 py-1 rounded border border-slate-700/50">
                                        Rp ${budgetJuta} JT
                                    </div>
                                </div>
                            </div>`;
                    });
                }
            } catch (e) { logError("Data Error: " + e.message); }
        }

        window.onload = () => { 
            initMap(); 
            initPusher();
            loadData(); 
            setInterval(loadData, 30000); 
            
            // Start Live Worker Tracking Simulation
            startSimulation();

            // For parent window communication (if used inside an iframe, or just self)
            window.map = map;
        };
    </script>
</body>
</html>
