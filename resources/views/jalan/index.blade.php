<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sismap - Manajemen Jalan</title>
    <meta name="description" content="Kelola dan pantau kondisi jalan berdasarkan provinsi dan kota di Maluku Utara.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        #road-map { height: 380px; border-radius: 1rem; z-index: 0; }
        .leaflet-popup-content-wrapper { background: #1e293b; color: #e2e8f0; border: 1px solid rgba(99,102,241,0.3); border-radius: 0.75rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .leaflet-popup-tip { background: #1e293b; }
        .leaflet-popup-close-button { color: #94a3b8 !important; }
        .condition-badge { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:11px; font-weight:600; }
        .badge-baik { background:rgba(16,185,129,0.2); color:#10b981; }
        .badge-rusak-ringan { background:rgba(245,158,11,0.2); color:#f59e0b; }
        .badge-rusak-berat { background:rgba(239,68,68,0.2); color:#ef4444; }
        .card-animate { animation: fadeUp 0.4s ease both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        .card-animate:nth-child(1){animation-delay:.05s} .card-animate:nth-child(2){animation-delay:.1s}
        .card-animate:nth-child(3){animation-delay:.15s} .card-animate:nth-child(4){animation-delay:.2s}
        .card-animate:nth-child(5){animation-delay:.25s} .card-animate:nth-child(6){animation-delay:.3s}
        select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236366f1' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-repeat:no-repeat; background-position:right .5rem center; background-size:1.5em 1.5em; padding-right:2.5rem; }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans flex flex-col min-h-screen">

    {{-- HEADER --}}
    <header class="bg-slate-800/60 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN</span>
            </div>
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Beranda</a>
                <a href="{{ route('jalan.index') }}" class="text-sm font-semibold text-white border-b-2 border-indigo-500 pb-0.5">Aset Jalan</a>
                <a href="{{ route('jembatan.index') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Aset Jembatan</a>
            </nav>
            <a href="{{ route('jalan.create') }}" id="btn-tambah-aset" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Aset
            </a>
        </div>
    </header>

    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- HERO STATS --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-1 tracking-tight">Daftar Aset Jalan</h1>
            <p class="text-slate-400 text-sm">Kelola dan pantau kondisi jalan berdasarkan provinsi dan kota. &mdash; <span class="text-indigo-400 font-medium">{{ $city ?: $province }}</span></p>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $statItems = [
                    ['label'=>'Total Jalan','value'=>$totalJalan,'icon'=>'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7','color'=>'indigo','unit'=>'ruas'],
                    ['label'=>'Total Panjang','value'=>number_format($totalPanjang,1),'icon'=>'M4 8h16M4 16h16','color'=>'purple','unit'=>'km'],
                    ['label'=>'Provinsi','value'=>$province ?: '-','icon'=>'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9','color'=>'cyan','unit'=>''],
                    ['label'=>'Kota','value'=>$city ?: 'Semua','icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','color'=>'emerald','unit'=>''],
                ];
                $colorMap = ['indigo'=>'from-indigo-500 to-indigo-700 shadow-indigo-500/20','purple'=>'from-purple-500 to-purple-700 shadow-purple-500/20','cyan'=>'from-cyan-500 to-cyan-700 shadow-cyan-500/20','emerald'=>'from-emerald-500 to-emerald-700 shadow-emerald-500/20'];
            @endphp
            @foreach($statItems as $s)
            <div class="bg-slate-800/70 border border-slate-700/50 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $colorMap[$s['color']] }} flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">{{ $s['label'] }}</p>
                    <p class="text-lg font-bold text-white truncate">{{ $s['value'] }} <span class="text-xs font-normal text-slate-400">{{ $s['unit'] }}</span></p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- FILTER BAR --}}
        <form id="filter-form" action="{{ route('jalan.index') }}" method="GET" class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[140px]">
                <label class="text-xs text-slate-400 mb-1 block">Provinsi</label>
                <select id="sel-province" name="province" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 text-sm rounded-xl p-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                    <option value="">-- Semua Provinsi --</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p }}" {{ $province == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="text-xs text-slate-400 mb-1 block">Kota / Kabupaten</label>
                <select id="sel-city" name="city" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 text-sm rounded-xl p-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                    <option value="">-- Semua Kota --</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ $city == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="text-xs text-slate-400 mb-1 block">Kecamatan</label>
                <select id="sel-district" name="district" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 text-sm rounded-xl p-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none">
                    <option value="">-- Semua Kecamatan --</option>
                    @foreach($districts as $d)
                        <option value="{{ $d }}" {{ $district == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('jalan.index') }}" class="text-sm text-slate-400 hover:text-white bg-slate-700/60 hover:bg-slate-700 border border-slate-600 px-4 py-2.5 rounded-xl transition-all">Reset</a>
        </form>

        {{-- MAP --}}
        @if($mapData->count() > 0)
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="text-sm font-semibold text-slate-200">Peta Sebaran Jalan</span>
                <span class="text-xs text-slate-500 ml-auto">{{ $mapData->count() }} titik ditampilkan</span>
            </div>
            <div id="road-map"></div>
        </div>
        @endif

        {{-- SUCCESS FLASH --}}
        @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-4 py-3 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- ROAD CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($jalans as $jalan)
            @php
                $cond = strtolower($jalan->condition_status ?? 'baik');
                $condClass = $cond === 'baik' ? 'badge-baik' : ($cond === 'sedang' ? 'badge-rusak-ringan' : 'badge-rusak-berat');
                $condDot   = $cond === 'baik' ? 'bg-emerald-400' : ($cond === 'sedang' ? 'bg-amber-400' : 'bg-red-400');
                $condLabel = $cond === 'baik' ? 'Baik' : ($cond === 'sedang' ? 'Sedang' : 'Rusak');
            @endphp
            <div class="card-animate bg-slate-800/80 border border-slate-700/50 rounded-2xl overflow-hidden hover:shadow-2xl hover:shadow-indigo-500/10 hover:border-indigo-500/30 transition-all duration-300 group flex flex-col">
                {{-- Road Photo or Map Thumbnail --}}
                <div class="h-36 bg-gradient-to-br from-slate-900 to-slate-800 relative flex items-center justify-center overflow-hidden">
                    @if($jalan->photo_url)
                        <img src="{{ url('uploads/others/pu/' . \Carbon\Carbon::parse($jalan->created_at)->format('Y') . '/' . $jalan->photo_url) }}" alt="{{ $jalan->road_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(0deg,transparent,transparent 24px,rgba(99,102,241,.4) 24px,rgba(99,102,241,.4) 25px), repeating-linear-gradient(90deg,transparent,transparent 24px,rgba(99,102,241,.4) 24px,rgba(99,102,241,.4) 25px);"></div>
                        @if($jalan->latitude && $jalan->longitude)
                        <div class="relative z-10 text-center">
                            <div class="w-10 h-10 rounded-full bg-indigo-500/20 border-2 border-indigo-400 flex items-center justify-center mx-auto mb-1 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            </div>
                            <p class="text-xs text-slate-400">{{ number_format($jalan->latitude,5) }}, {{ number_format($jalan->longitude,5) }}</p>
                        </div>
                        @else
                        <svg class="w-10 h-10 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    @endif
                    <div class="absolute top-2 right-2">
                        <span class="condition-badge {{ $condClass }}">
                            <span class="inline-block w-1.5 h-1.5 rounded-full {{ $condDot }} mr-1"></span>{{ $condLabel }}
                        </span>
                    </div>
                    <div class="absolute bottom-2 left-2 bg-slate-900/80 backdrop-blur text-indigo-300 text-xs font-bold px-2 py-0.5 rounded-md">{{ number_format($jalan->length_km, 2) }} km</div>
                </div>

                <div class="p-4 flex flex-col flex-grow">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h3 class="text-base font-bold text-white group-hover:text-indigo-400 transition-colors line-clamp-1">{{ $jalan->road_name }}</h3>
                    </div>
                    <p class="text-xs text-slate-500 font-mono mb-3">{{ $jalan->road_code ?: '-' }}</p>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs mb-3">
                        <div>
                            <span class="text-slate-500">Lebar</span>
                            <p class="text-slate-300 font-semibold">{{ $jalan->width_m ?? '-' }} m</p>
                        </div>
                        <div>
                            <span class="text-slate-500">Elevasi</span>
                            <p class="text-slate-300 font-semibold">{{ $jalan->elevation ?? '-' }} m</p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-500">Kecamatan</span>
                            <p class="text-slate-300 font-semibold">{{ $jalan->region->district ?? '-' }}</p>
                        </div>
                    </div>

                    @if($jalan->description)
                    <p class="text-xs text-slate-500 italic line-clamp-1 mb-3">{{ $jalan->description }}</p>
                    @endif

                    <div class="mt-auto pt-3 border-t border-slate-700/50 flex gap-2">
                        <a href="{{ route('jalan.show', $jalan->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 py-2 px-3 rounded-xl transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                        <a href="{{ route('jalan.edit', $jalan->id) }}" class="inline-flex items-center justify-center p-2 text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 rounded-xl transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form action="{{ route('jalan.destroy', $jalan->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center p-2 text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-xl transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-slate-800/40 rounded-2xl border border-dashed border-slate-700">
                <svg class="w-14 h-14 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <h3 class="text-lg font-semibold text-slate-300 mb-1">Tidak Ada Data</h3>
                <p class="text-slate-500 text-sm mb-4">Belum ada data jalan untuk filter yang dipilih.</p>
                <a href="{{ route('jalan.create') }}" class="text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-xl transition-all">+ Tambah Aset Pertama</a>
            </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($jalans->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $jalans->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </main>

    {{-- Leaflet Map Init --}}
    @if($mapData->count() > 0)
    <script>
    (function() {
        var roads = @json($mapData);
        var map = L.map('road-map', { 
            zoomControl: true, 
            scrollWheelZoom: false,
            preferCanvas: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        var condColors = { 'baik': '#10b981', 'sedang': '#f59e0b', 'rusak': '#ef4444', 'rusak_ringan': '#f97316', 'rusak_berat': '#e11d48' };
        var bounds = [];
        var markers = {}; // Store markers by id

        function getPopupContent(r) {
            return '<div style="min-width:180px">' +
                '<p style="font-weight:700;font-size:13px;margin-bottom:4px">' + r.road_name + '</p>' +
                '<p style="font-size:11px;color:#94a3b8;margin-bottom:6px;font-family:monospace">' + (r.road_code||'-') + '</p>' +
                '<div style="display:flex;gap:12px;font-size:11px">' +
                '<span>📏 ' + r.length_km + ' km</span>' +
                '<span>📐 ' + (r.width_m || '-') + ' m</span>' +
                '</div>' +
                '<div style="margin-top:6px;font-size:11px;font-weight:bold;color:' + (condColors[(r.condition_status||'').toLowerCase()] || '#6366f1') + '">Kondisi: ' + (r.condition_status||'Baik').toUpperCase() + '</div>' +
                '</div>';
        }

        roads.forEach(function(r) {
            if (!r.latitude || !r.longitude) return;
            var lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
            bounds.push([lat, lng]);

            var color = condColors[(r.condition_status || '').toLowerCase()] || '#6366f1';
            var marker = L.circleMarker([lat, lng], {
                radius: 8, fillColor: color, color: '#fff',
                weight: 2, opacity: 1, fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(getPopupContent(r));
            markers[r.id] = marker;
            marker._roadData = r;
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
        } else {
            map.setView([-0.7898, 127.3746], 12);
        }

        // --- Realtime Sync ---
        const socket = io('http://' + window.location.hostname + ':3000');
        
        socket.on('connect', () => {
            console.log('[Admin] Socket connected for map updates');
        });

        socket.on('data-refresh', (payload) => {
            const assetId = payload.asset_id || payload.id;
            const marker = markers[assetId];
            if (marker && payload.condition) {
                const color = condColors[payload.condition.toLowerCase()] || '#6366f1';
                marker.setStyle({ fillColor: color });
                marker._roadData.condition_status = payload.condition;
                marker.setPopupContent(getPopupContent(marker._roadData));
                
                // Show notification briefly
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 bg-emerald-500/90 text-white px-4 py-2 rounded-xl shadow-lg z-50 text-sm font-bold';
                toast.innerText = '🔄 Data Peta Diperbarui: ' + marker._roadData.road_name;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);

                // Auto zoom if damaged (rusak)
                if (['rusak', 'rusak_berat', 'rusak_ringan'].includes(payload.condition.toLowerCase())) {
                    map.setView(marker.getLatLng(), 16);
                    marker.openPopup();
                }
            }
        });

        socket.on('road-selected', (payload) => {
            const assetId = payload.asset_id || payload.id;
            const marker = markers[assetId];
            if (marker) {
                map.setView(marker.getLatLng(), 17);
                marker.openPopup();
            } else if (payload.lat && payload.lng) {
                map.setView([payload.lat, payload.lng], 16);
            }
        });

    })();
    </script>
    @endif

</body>
</html>
