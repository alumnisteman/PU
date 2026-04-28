<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jalan->road_name }} - Detail Jalan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans min-h-screen selection:bg-indigo-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('jalan.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-medium text-sm">Kembali</span>
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN</h1>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="bg-slate-800/80 rounded-3xl overflow-hidden border border-slate-700/50 shadow-2xl mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Image Side (Map Placeholder or Photo) -->
                <div class="h-64 lg:h-auto relative bg-slate-900 border-r border-slate-700/30">
                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden">
                        @if($jalan->photo_url)
                            <img src="{{ url('uploads/others/pu/' . \Carbon\Carbon::parse($jalan->created_at)->format('Y') . '/' . $jalan->photo_url) }}" alt="{{ $jalan->road_name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
                                <span class="text-white/60 text-[10px] font-bold uppercase tracking-widest bg-slate-900/40 backdrop-blur px-2 py-1 rounded">Foto Kondisi Terkini</span>
                                <span class="text-white/40 text-[10px] font-medium">3D Map: Versi Berikutnya</span>
                            </div>
                        @else
                            <svg class="w-20 h-20 text-slate-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <span class="text-slate-500 font-medium px-8 text-center text-sm">Visualisasi Peta Digital 3D akan tersedia di versi berikutnya</span>
                        @endif
                    </div>
                    <div class="absolute top-4 left-4 z-10">
                        <span class="bg-indigo-500/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                            GPS: {{ $jalan->latitude ? number_format($jalan->latitude, 5) . ', ' . number_format($jalan->longitude, 5) : 'N/A' }}
                        </span>
                    </div>
                </div>

                <!-- Content Side -->
                <div class="p-8 lg:p-12 flex flex-col justify-center">
                    <div class="mb-6">
                        <span class="inline-block px-3 py-1 bg-slate-700/50 text-indigo-300 border border-indigo-500/30 rounded-full text-xs font-semibold tracking-wider uppercase mb-4">Kode Ruas: {{ $jalan->road_code ?: '-' }}</span>
                        <h2 class="text-4xl font-extrabold text-white mb-4 tracking-tight">{{ $jalan->road_name }}</h2>
                        <p class="text-slate-400 text-lg leading-relaxed">{{ $jalan->description ?: 'Belum ada keterangan untuk aset jalan ini.' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/30">
                            <div class="text-slate-500 text-sm mb-1">Panjang Total</div>
                            <div class="text-2xl font-bold text-white">{{ number_format($jalan->length_km, 2) }} <span class="text-lg text-slate-400 font-normal">km</span></div>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/30">
                            <div class="text-slate-500 text-sm mb-1">Lebar Jalan</div>
                            <div class="text-2xl font-bold text-white">{{ $jalan->width_m ?? '-' }} <span class="text-lg text-slate-400 font-normal">m</span></div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-8">
                        <a href="{{ route('jalan.edit', $jalan->id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-indigo-600/25">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Aset
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Foto Kondisi Terkini Section -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white">Foto Kondisi Terkini</h3>
            </div>
            
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-3xl overflow-hidden p-4">
                @if($jalan->photo_url)
                    <div class="relative group">
                        <img src="{{ url('uploads/others/pu/' . \Carbon\Carbon::parse($jalan->created_at)->format('Y') . '/' . $jalan->photo_url) }}" alt="{{ $jalan->road_name }}" class="w-full h-auto max-h-[600px] object-cover rounded-2xl shadow-2xl">
                        <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-white/10"></div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-20 text-slate-500">
                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium text-lg">Foto belum diunggah</p>
                        <a href="{{ route('jalan.edit', $jalan->id) }}" class="mt-4 text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Unggah Foto Sekarang →</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Detail Wilayah & Status Cards (Original) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Kondisi Jalan -->
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-3xl p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Status Kondisi</h3>
                </div>

                <div class="bg-slate-900/50 border border-slate-700 rounded-xl p-6 text-center">
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-bold tracking-wider uppercase
                        {{ $jalan->condition_status === 'baik' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/50' : 
                          ($jalan->condition_status === 'sedang' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/50' : 
                          ($jalan->condition_status === 'rusak_ringan' ? 'bg-orange-500/20 text-orange-400 border border-orange-500/50' :
                          'bg-red-500/20 text-red-400 border border-red-500/50')) }}">
                        @if($jalan->condition_status === 'baik') Baik
                        @elseif($jalan->condition_status === 'sedang') Sedang
                        @elseif($jalan->condition_status === 'rusak_ringan') Rusak Ringan
                        @elseif($jalan->condition_status === 'rusak_berat') Rusak Berat
                        @else Rusak
                        @endif
                    </span>
                    <p class="text-slate-500 text-sm mt-4">Kondisi saat ini diperbarui berdasarkan laporan terakhir.</p>
                </div>
            </div>

            <!-- Detail Wilayah -->
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-3xl p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Detail Wilayah</h3>
                </div>

                <div class="bg-slate-900/50 border border-slate-700 rounded-xl p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-slate-500 text-xs block mb-1">Provinsi</span>
                            <span class="text-white text-sm font-medium">{{ $jalan->region->province ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 text-xs block mb-1">Kota/Kabupaten</span>
                            <span class="text-white text-sm font-medium">{{ $jalan->region->city ?? '-' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-500 text-xs block mb-1">Kecamatan</span>
                            <span class="text-white text-sm font-medium">{{ $jalan->region->district ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estimasi Biaya Section -->
        <div class="mt-8 bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-3xl p-8 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Estimasi Biaya Material</h3>
                        <p class="text-slate-500 text-sm mt-1">Perkiraan biaya konstruksi berdasarkan dimensi jalan.</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 text-xs font-bold uppercase tracking-widest block mb-1">Total Biaya</span>
                    <span class="text-3xl font-black text-emerald-500">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-700/50">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/50 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Nama Material</th>
                            <th class="px-6 py-4">Volume Kebutuhan</th>
                            <th class="px-6 py-4">Harga Satuan</th>
                            <th class="px-6 py-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/30">
                        @forelse($roadMaterials as $rm)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-200">{{ $rm->material->name }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $rm->volume }} {{ $rm->material->unit }}</td>
                            <td class="px-6 py-4 text-slate-400">Rp {{ number_format($rm->material->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-indigo-400 text-right">Rp {{ number_format($rm->volume * $rm->material->price, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">Data estimasi biaya belum tersedia untuk jalan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>

