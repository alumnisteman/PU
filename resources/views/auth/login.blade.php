<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | North Maluku ICC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #020617; }
        .glass { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glow { box-shadow: 0 0 40px -10px rgba(59, 130, 246, 0.3); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 overflow-hidden relative">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 rounded-full blur-[120px] animate-pulse" style="z-index:-1;pointer-events:none;"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/10 rounded-full blur-[120px] animate-pulse" style="z-index:-1;pointer-events:none;animation-delay:2s;"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-6 shadow-xl shadow-blue-600/20 glow">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-2 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">North Maluku</h1>
            <p class="text-slate-500 font-medium tracking-widest text-[10px] uppercase">Infrastructure Control Center</p>
        </div>

        <div class="glass p-8 rounded-[2.5rem] shadow-2xl glow border border-white/5">
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Admin Email</label>
                    <input type="email" name="email" id="email" required 
                           class="w-full bg-slate-900/50 border border-slate-800 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder:text-slate-700 shadow-inner"
                           placeholder="admin@northmaluku.id">
                </div>
                
                <div>
                    <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 ml-1">Portal Password</label>
                    <input type="password" name="password" id="password" required 
                           class="w-full bg-slate-900/50 border border-slate-800 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder:text-slate-700 shadow-inner"
                           placeholder="••••••••">
                </div>

                @if ($errors->any())
                <div class="p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-rose-400 text-xs font-bold">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-blue-600/30 transition-all active:scale-[0.98] tracking-widest text-xs">
                    MASUK KE PORTAL
                </button>
            </form>
        </div>

        <p class="text-center mt-10 text-slate-600 text-[10px] font-bold tracking-widest uppercase">
            &copy; 2026 North Maluku Infrastructure
        </p>
    </div>
<script>
// Bunuh semua Service Worker lama agar tidak ada cache yang mengganggu
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(registrations => {
        registrations.forEach(reg => reg.unregister());
    });
    caches.keys().then(keys => keys.forEach(key => caches.delete(key)));
}
</script>
</body>
</html>
