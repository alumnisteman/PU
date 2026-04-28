<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Si-DILAN SISMAP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans min-h-screen selection:bg-indigo-500 selection:text-white overflow-x-hidden">

    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[120px]"></div>
    </div>

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN</h1>
            </div>
            
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-white transition-colors border-b-2 border-indigo-500 pb-1">Beranda</a>
                <a href="{{ route('jalan.index') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Aset Jalan</a>
                <a href="{{ route('jembatan.index') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Aset Jembatan</a>
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ optional(auth()->user()->level)->level_name ?? 'Level' }}</div>
                            <div class="text-xs font-bold text-white">{{ optional(auth()->user())->user_fullname ?? 'User' }}</div>
                        </div>
                        <div class="group relative">
                            <button class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-600/20 border-2 border-white/10 hover:border-indigo-400 transition-all">
                                {{ substr(optional(auth()->user())->user_fullname ?? 'U', 0, 1) }}
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-slate-800 border border-white/10 rounded-2xl shadow-2xl py-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all z-50 overflow-hidden">
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white">Admin Portal</a>
                                <hr class="my-2 border-white/5">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-400 hover:bg-rose-500/10 transition-colors">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-600/20 transition-all">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Welcome Hero -->
        <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600/20 to-purple-600/20 rounded-[2.5rem] p-8 md:p-12 mb-10 border border-white/5">
            <div class="relative z-10 max-w-2xl">
                <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4 leading-tight">North Maluku <br/> <span class="text-indigo-400">Infrastructure Control Center</span></h2>
                <p class="text-slate-400 text-lg mb-8 leading-relaxed">Pusat Kendali Terpadu Infrastruktur Jalan dan Jembatan Maluku Utara.</p>
                
                <form action="{{ route('admin.dashboard') }}" method="GET" class="relative max-w-md mb-8 group">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari Nama Jalan atau Jembatan..." class="w-full bg-slate-900/80 border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-white focus:ring-2 focus:ring-indigo-500 transition-all group-hover:border-white/20">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    @if($stats['search_results'])
                        <div class="absolute top-full left-0 w-full mt-2 bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="p-2 space-y-1">
                                @foreach($stats['search_results']['jalan'] as $res)
                                    <a href="{{ route('jalan.show', $res->jalan_id) }}" class="flex items-center gap-3 p-3 hover:bg-slate-700 rounded-xl transition-all">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                            </svg>
                                        </div>
                                        <span class="text-sm text-slate-200">{{ $res->jalan_nama }} (Jalan)</span>
                                    </a>
                                @endforeach
                                @foreach($stats['search_results']['jembatan'] as $res)
                                    <a href="{{ route('jembatan.show', $res->jembatan_id) }}" class="flex items-center gap-3 p-3 hover:bg-slate-700 rounded-xl transition-all">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2" />
                                            </svg>
                                        </div>
                                        <span class="text-sm text-slate-200">{{ $res->jembatan_nama }} (Jembatan)</span>
                                    </a>
                                @endforeach
                                @if(count($stats['search_results']['jalan']) == 0 && count($stats['search_results']['jembatan']) == 0)
                                    <div class="p-4 text-center text-slate-500 text-sm">Tidak ada hasil ditemukan</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </form>

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('jalan.index') }}" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl transition-all shadow-xl shadow-indigo-600/25 flex items-center gap-2">
                        Mulai Kelola Jalan
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-1/2 h-full hidden lg:block">
                <div class="w-full h-full opacity-20 bg-[url('https://images.unsplash.com/photo-1545143333-636a6619f74f?auto=format&fit=crop&q=80')] bg-cover bg-center"></div>
                <div class="absolute inset-0 bg-gradient-to-l from-transparent to-slate-900/80"></div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 p-6 rounded-3xl hover:border-indigo-500/30 transition-all group">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 mb-4 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </div>
                <div class="text-slate-500 text-sm font-medium">Total Ruas Jalan</div>
                <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($stats['jalan_count']) }}</div>
            </div>

            <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 p-6 rounded-3xl hover:border-purple-500/30 transition-all group">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-500 mb-4 group-hover:bg-purple-500 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="text-slate-500 text-sm font-medium">Total Jembatan</div>
                <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($stats['jembatan_count']) }}</div>
            </div>

            <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 p-6 rounded-3xl hover:border-green-500/30 transition-all group">
                <div class="w-12 h-12 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-500 mb-4 group-hover:bg-green-500 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="text-slate-500 text-sm font-medium">Panjang Jalan (KM)</div>
                <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($stats['total_panjang_jalan'], 1) }}</div>
            </div>

            <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 p-6 rounded-3xl hover:border-pink-500/30 transition-all group">
                <div class="w-12 h-12 rounded-2xl bg-pink-500/10 flex items-center justify-center text-pink-500 mb-4 group-hover:bg-pink-500 group-hover:text-white transition-all">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="text-slate-500 text-sm font-medium">Pengguna Aktif</div>
                <div class="text-3xl font-extrabold text-white mt-1">{{ number_format($stats['user_count']) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- GIS Map Section -->
            <div class="lg:col-span-3 bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 rounded-3xl p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">Visualisasi Spasial Aset</h3>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg text-xs font-bold uppercase tracking-wider">Live View</span>
                    </div>
                </div>
                <div id="map" class="h-[400px] rounded-2xl bg-slate-900 border border-slate-700/50 relative overflow-hidden">
                    <!-- Leaflet Map -->
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 rounded-3xl p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-bold text-white">Modul Sistem Terintegrasi</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <a href="{{ route('jalan.index') }}" class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700 hover:border-indigo-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-bold">Manajemen Jalan</h4>
                                <p class="text-slate-500 text-sm mt-1">Data ruas, kondisi, dan penanganan.</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('jembatan.index') }}" class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700 hover:border-purple-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-500 group-hover:bg-purple-500 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-bold">Manajemen Jembatan</h4>
                                <p class="text-slate-500 text-sm mt-1">Data bentang, konstruksi, dan lokasi.</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('gallery.index') }}" class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700 hover:border-pink-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-pink-500/10 flex items-center justify-center text-pink-500 group-hover:bg-pink-500 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-bold">Media Center</h4>
                                <p class="text-slate-500 text-sm mt-1">Galeri foto dokumentasi aset.</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('users.index') }}" class="p-6 bg-slate-900/50 rounded-2xl border border-slate-700 hover:border-amber-500/50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-bold">Manajemen User</h4>
                                <p class="text-slate-500 text-sm mt-1">Kelola hak akses dan surveyor.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Stats Chart -->
            <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 rounded-3xl p-8">
                <h3 class="text-xl font-bold text-white mb-6">Analisis Kondisi Jalan</h3>
                <div id="conditionChart" class="min-h-[250px]"></div>
                
                <div class="mt-6 p-6 bg-indigo-600/10 border border-indigo-500/20 rounded-2xl">
                    <div class="text-indigo-300 font-bold mb-1 italic">Si-DILAN v2.0</div>
                    <p class="text-slate-400 text-xs">Modernizing legacy infrastructure for the future.</p>
                </div>
            </div>
        </div>
    </main>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Map Initialization
            var map = L.map('map').setView([-0.638, 127.48], 10); // Center on Halsel/Malut area
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Plot Markers
            var jalanMarkers = @json($stats['map_markers']['jalan']);
            var jembatanMarkers = @json($stats['map_markers']['jembatan']);

            jalanMarkers.forEach(function(item) {
                if (item.jalan_llh) {
                    var coords = item.jalan_llh.split(',');
                    if (coords.length === 2) {
                        L.circleMarker([parseFloat(coords[0]), parseFloat(coords[1])], {
                            radius: 5,
                            fillColor: "#6366f1",
                            color: "#fff",
                            weight: 1,
                            opacity: 1,
                            fillOpacity: 0.8
                        }).addTo(map).bindPopup("<b>Jalan:</b> " + item.jalan_nama);
                    }
                }
            });

            jembatanMarkers.forEach(function(item) {
                if (item.jembatan_llh) {
                    var coords = item.jembatan_llh.split(',');
                    if (coords.length === 2) {
                        L.circleMarker([parseFloat(coords[0]), parseFloat(coords[1])], {
                            radius: 7,
                            fillColor: "#d946ef",
                            color: "#fff",
                            weight: 1,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(map).bindPopup("<b>Jembatan:</b> " + item.jembatan_nama);
                    }
                }
            });

            // Chart Initialization
            var options = {
                series: [
                    {{ $stats['condition_stats']['Baik'] }},
                    {{ $stats['condition_stats']['Sedang'] }},
                    {{ $stats['condition_stats']['Rusak Ringan'] }},
                    {{ $stats['condition_stats']['Rusak Berat'] }}
                ],
                chart: {
                    type: 'donut',
                    height: 320,
                    foreColor: '#94a3b8'
                },
                labels: ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat'],
                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            background: 'transparent',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    color: '#94a3b8',
                                    offsetY: -10
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 'bold',
                                    color: '#ffffff',
                                    offsetY: 10,
                                    formatter: function(val) {
                                        return val + "%"
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Rata-rata',
                                    color: '#94a3b8',
                                    formatter: function(w) {
                                        return "85%"
                                    }
                                }
                            }
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#conditionChart"), options);
            chart.render();
        });
    </script>

</body>
</html>
