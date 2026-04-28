<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jembatan->jembatan_nama }} - Detail Jembatan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans min-h-screen selection:bg-indigo-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('jembatan.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-medium text-sm">Kembali</span>
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">Si-DILAN</h1>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="bg-slate-800/80 rounded-3xl overflow-hidden border border-slate-700/50 shadow-2xl mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Image Side -->
                <div class="h-64 lg:h-auto relative bg-slate-900">
                    @php
                        $latestDetailWithPhoto = $jembatan->details->whereNotNull('detail_foto_alias')->where('detail_foto_alias', '!=', '')->last();
                    @endphp
                    @if($latestDetailWithPhoto)
                        <img src="{{ url('uploads/others/pu/' . $latestDetailWithPhoto->detail_tahun . '/' . $latestDetailWithPhoto->detail_foto_alias) }}" alt="Foto {{ $jembatan->jembatan_nama }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900">
                            <svg class="w-20 h-20 text-slate-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-slate-500 font-medium">Gambar tidak tersedia</span>
                        </div>
                    @endif
                    <div class="absolute top-4 left-4">
                        <span class="bg-purple-500/90 backdrop-blur text-white text-sm font-bold px-3 py-1.5 rounded-lg shadow-lg">
                            Titik Tengah: {{ $jembatan->jembatan_llh ?: 'Tidak Diketahui' }}
                        </span>
                    </div>
                </div>

                <!-- Content Side -->
                <div class="p-8 lg:p-12 flex flex-col justify-center">
                    <div class="mb-6">
                        <span class="inline-block px-3 py-1 bg-slate-700/50 text-purple-300 border border-purple-500/30 rounded-full text-xs font-semibold tracking-wider uppercase mb-4">Kode Jembatan: {{ $jembatan->jembatan_kode ?: '-' }}</span>
                        <h2 class="text-4xl font-extrabold text-white mb-4 tracking-tight">{{ $jembatan->jembatan_nama }}</h2>
                        <p class="text-slate-400 text-lg leading-relaxed">{{ $jembatan->jembatan_keterangan ?: 'Belum ada keterangan untuk aset jembatan ini.' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/30">
                            <div class="text-slate-500 text-sm mb-1">Bentang Total</div>
                            <div class="text-2xl font-bold text-white">{{ number_format($jembatan->jembatan_panjang, 2) }} <span class="text-lg text-slate-400 font-normal">m</span></div>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/30">
                            <div class="text-slate-500 text-sm mb-1">Lebar Jembatan</div>
                            <div class="text-2xl font-bold text-white">{{ $jembatan->jembatan_lebar }} <span class="text-lg text-slate-400 font-normal">m</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Data Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
            
            <!-- Spesifikasi Detail -->
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-3xl p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-pink-500/20 flex items-center justify-center text-pink-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Data Teknis & Metadata</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($jembatan->details as $detail)
                        <div class="bg-slate-900/50 border border-slate-700 rounded-2xl p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between border-b border-slate-800 pb-2">
                                    <span class="text-slate-500 text-sm">Konstruksi</span>
                                    <span class="text-white text-sm font-semibold">{{ $detail->detail_konstruksi ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-slate-800 pb-2">
                                    <span class="text-slate-500 text-sm">Tahun Bangun</span>
                                    <span class="text-white text-sm font-semibold">{{ $detail->detail_tahun_bangun ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-slate-800 pb-2">
                                    <span class="text-slate-500 text-sm">Tipe Jembatan</span>
                                    <span class="text-white text-sm font-semibold">{{ $detail->detail_tipe ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 text-sm">Status</span>
                                    <span class="px-2 py-0.5 bg-green-500/10 text-green-400 rounded-md text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-slate-500 bg-slate-900/30 rounded-xl border border-dashed border-slate-700">
                            Belum ada data teknis terperinci untuk jembatan ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </main>

</body>
</html>
