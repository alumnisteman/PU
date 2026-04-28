<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sismap - Manajemen Jembatan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans flex flex-col min-h-screen selection:bg-indigo-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg shadow-purple-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">Si-DILAN</h1>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Beranda</a>
                <a href="{{ route('jalan.index') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Aset Jalan</a>
                <a href="{{ route('jembatan.index') }}" class="text-sm font-bold text-white transition-colors border-b-2 border-purple-500 pb-1">Aset Jembatan</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">Daftar Aset Jembatan</h2>
                <p class="text-slate-400 text-sm">Kelola dan pantau kondisi jembatan berdasarkan wilayah.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <form action="{{ route('jembatan.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <select name="propinsi" class="bg-slate-800 border border-slate-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 text-white placeholder-slate-400 appearance-none">
                        <option value="">-- Semua Provinsi --</option>
                        @foreach($propinsis as $prop)
                            <option value="{{ $prop->propinsi_id }}" {{ request('propinsi') == $prop->propinsi_id ? 'selected' : '' }}>
                                {{ $prop->propinsi_nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="text-white bg-slate-700 hover:bg-slate-600 focus:ring-4 focus:outline-none focus:ring-slate-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all">Filter</button>
                </form>
                <a href="{{ route('jembatan.create') }}" class="text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:outline-none focus:ring-purple-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-300 shadow-lg shadow-purple-500/25 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jembatan
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($jembatans as $jemb)
                <div class="bg-slate-800/80 backdrop-blur-sm border border-slate-700/50 rounded-2xl overflow-hidden hover:shadow-xl hover:shadow-purple-500/10 hover:border-purple-500/30 transition-all duration-300 group flex flex-col h-full">
                    <div class="h-48 bg-slate-900 relative overflow-hidden">
                        @if($jemb->jembatan_foto)
                            <img src="{{ url('uploads/others/pu/' . \Carbon\Carbon::parse($jemb->jembatan_dibuat_pada)->format('Y') . '/' . $jemb->jembatan_foto) }}" alt="Foto Jembatan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900">
                                <svg class="w-12 h-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        @endif
                        <div class="absolute top-3 right-3 bg-purple-500/90 backdrop-blur text-white text-xs font-bold px-2.5 py-1 rounded-md shadow-sm">
                            {{ number_format($jemb->jembatan_panjang, 2) }} m
                        </div>
                    </div>
                    
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-white mb-1 group-hover:text-purple-400 transition-colors line-clamp-1" title="{{ $jemb->jembatan_nama }}">{{ $jemb->jembatan_nama }}</h3>
                        <p class="text-slate-400 text-xs font-medium mb-4 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            Kode: {{ $jemb->jembatan_kode ?: '-' }}
                        </p>
                        
                        <div class="space-y-2 mt-auto">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Lebar:</span>
                                <span class="text-slate-300 font-medium">{{ $jemb->jembatan_lebar }} m</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Konstruksi:</span>
                                <span class="text-slate-300 font-medium line-clamp-1 text-right ml-4">{{ $jemb->jembatan_konstruksi ?: '-' }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-slate-700/50 flex gap-2">
                            <a href="{{ route('jembatan.show', $jemb->jembatan_id) }}" class="flex-1 inline-flex items-center justify-center gap-2 text-xs font-semibold text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 py-2 px-3 rounded-xl transition-colors">
                                Detail
                            </a>
                            <a href="{{ route('jembatan.edit', $jemb->jembatan_id) }}" class="inline-flex items-center justify-center p-2 text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('jembatan.destroy', $jemb->jembatan_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center p-2 text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-xl transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 flex flex-col items-center justify-center text-center bg-slate-800/50 rounded-2xl border border-dashed border-slate-700">
                    <svg class="w-16 h-16 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-300 mb-1">Tidak Ada Data</h3>
                    <p class="text-slate-500 text-sm">Belum ada data jembatan yang tersedia.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $jembatans->links('pagination::tailwind') }}
        </div>
    </main>
</body>
</html>
