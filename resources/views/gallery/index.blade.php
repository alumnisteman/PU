<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Center - Sismap Gallery</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-200 antialiased font-sans min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-slate-900/80 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN Gallery</h1>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Beranda</a>
                <a href="{{ route('gallery.index') }}" class="text-sm font-bold text-white transition-colors border-b-2 border-indigo-500 pb-1">Media Center</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-12">
            <h2 class="text-4xl font-extrabold text-white mb-2 tracking-tight">Pusat Dokumentasi Aset</h2>
            <p class="text-slate-400 text-lg">Kumpulan dokumentasi visual seluruh aset jalan dan jembatan.</p>
        </div>

        <!-- Jalan Gallery -->
        <div class="mb-16">
            <h3 class="text-2xl font-bold text-indigo-400 mb-6 flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                Foto Aset Jalan
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($jalanPhotos as $photo)
                    <div class="group relative aspect-square bg-slate-900 rounded-3xl overflow-hidden border border-white/5 hover:border-indigo-500/50 transition-all shadow-xl">
                        <img src="{{ url('uploads/others/pu/' . $photo->detail_tahun . '/' . $photo->detail_foto_alias) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="{{ optional($photo->jalan)->jalan_nama }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-5">
                            <span class="text-white font-bold text-sm line-clamp-1">{{ optional($photo->jalan)->jalan_nama }}</span>
                            <a href="{{ route('jalan.show', $photo->detail_jalan_id) }}" class="text-indigo-400 text-[10px] font-bold uppercase mt-1">Lihat Detail →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Jembatan Gallery -->
        <div>
            <h3 class="text-2xl font-bold text-purple-400 mb-6 flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2" />
                </svg>
                Foto Aset Jembatan
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($jembatanPhotos as $photo)
                    <div class="group relative aspect-square bg-slate-900 rounded-3xl overflow-hidden border border-white/5 hover:border-purple-500/50 transition-all shadow-xl">
                        <img src="{{ url('uploads/others/pu/' . $photo->detail_tahun . '/' . $photo->detail_foto_alias) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="{{ optional($photo->jembatan)->jembatan_nama }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-5">
                            <span class="text-white font-bold text-sm line-clamp-1">{{ optional($photo->jembatan)->jembatan_nama }}</span>
                            <a href="{{ route('jembatan.show', $photo->detail_jembatan_id) }}" class="text-purple-400 text-[10px] font-bold uppercase mt-1">Lihat Detail →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

</body>
</html>
