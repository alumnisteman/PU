<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISMAP Pulse Command Center</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; background: #0f172a; color: white; overflow: hidden; }
        .leaflet-popup-content-wrapper { background: #1e293b; color: white; border: 1px solid #334155; }
        .leaflet-popup-tip { background: #1e293b; border: 1px solid #334155; }
    </style>
</head>
<body>
    <div id="app" class="h-screen w-screen grid grid-cols-4 gap-6 p-6">
        <!-- Main Map (Left) -->
        <div class="col-span-3 h-full relative">
            <map-view></map-view>
            
            <!-- Floating Header -->
            <div class="absolute top-6 left-6 right-6 z-[1000] flex justify-between items-start pointer-events-none">
                <div class="bg-slate-900/80 backdrop-blur-md p-4 rounded-xl border border-slate-700 shadow-2xl flex items-center gap-4 pointer-events-auto">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">NORTH MALUKU</h1>
                        <p class="text-xs text-slate-400 font-bold tracking-widest uppercase">Infrastructure Control Center</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pointer-events-auto">
                    @auth
                        <div class="bg-slate-900/80 backdrop-blur-md p-3 rounded-xl border border-slate-700 shadow-2xl flex items-center gap-4">
                            <div class="text-right hidden md:block">
                                <div class="text-[8px] font-black text-blue-400 uppercase tracking-widest">{{ optional(auth()->user()->level)->level_name ?? 'Level' }}</div>
                                <div class="text-[10px] font-bold text-white">{{ optional(auth()->user())->user_fullname ?? 'User' }}</div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="p-2.5 bg-rose-500/10 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-all shadow-lg shadow-rose-500/20 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-2xl shadow-blue-600/30 transition-all">Login Portal</a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="col-span-1 h-full flex flex-col gap-6">
            <!-- Stats -->
            <div class="h-1/3">
                <stats-panel></stats-panel>
            </div>
            
            <!-- Network Graph -->
            <div class="h-2/3 relative">
                <div class="absolute top-4 left-4 z-[1000]">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Network Topology
                    </h3>
                </div>
                <pulse-graph></pulse-graph>
            </div>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('SW registered:', reg);
                }).catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</body>
</html>