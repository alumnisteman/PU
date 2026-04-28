<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Aset Jalan - Sismap</title>
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
                <span class="font-medium text-sm">Batal</span>
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN</h1>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-white mb-2">Tambah Aset Jalan Baru</h2>
            <p class="text-slate-400">Masukkan detail aset jalan ke dalam pangkalan data.</p>
        </div>

        <form action="{{ route('jalan.store') }}" method="POST" enctype="multipart/form-data" class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-3xl p-8 sm:p-10 shadow-2xl">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Location Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-indigo-400 border-b border-slate-700 pb-2">Informasi Wilayah</h3>
                    
                    <div>
                        <label for="region_id" class="block text-sm font-medium text-slate-300 mb-2">Wilayah / Regional <span class="text-red-500">*</span></label>
                        <select name="region_id" id="region_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white">
                            <option value="">Pilih Wilayah</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->province }} - {{ $region->city }} - {{ $region->district }}</option>
                            @endforeach
                        </select>
                        @error('region_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-slate-300 mb-2">Latitude</label>
                            <input type="text" name="latitude" id="latitude" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500" placeholder="Contoh: -0.79012">
                            @error('latitude') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-slate-300 mb-2">Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500" placeholder="Contoh: 127.38411">
                            @error('longitude') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="elevation" class="block text-sm font-medium text-slate-300 mb-2">Elevasi (mdpl)</label>
                        <input type="number" step="0.01" name="elevation" id="elevation" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500">
                        @error('elevation') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Asset Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-400 border-b border-slate-700 pb-2">Identitas Aset</h3>
                    
                    <div>
                        <label for="road_code" class="block text-sm font-medium text-slate-300 mb-2">Kode Ruas Jalan</label>
                        <input type="text" name="road_code" id="road_code" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white" placeholder="Opsi">
                        @error('road_code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="road_name" class="block text-sm font-medium text-slate-300 mb-2">Nama Ruas Jalan <span class="text-red-500">*</span></label>
                        <input type="text" name="road_name" id="road_name" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white" placeholder="Nama Jalan">
                        @error('road_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="length_km" class="block text-sm font-medium text-slate-300 mb-2">Panjang (km) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="length_km" id="length_km" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            @error('length_km') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="width_m" class="block text-sm font-medium text-slate-300 mb-2">Lebar (m)</label>
                            <input type="number" step="0.01" name="width_m" id="width_m" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            @error('width_m') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="condition_status" class="block text-sm font-medium text-slate-300 mb-2">Status Kondisi</label>
                        <select name="condition_status" id="condition_status" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            <option value="baik">Baik</option>
                            <option value="sedang">Sedang</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                        @error('condition_status') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Group -->
            <div class="space-y-6 pt-6 border-t border-slate-700/50">
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Keterangan Tambahan</label>
                    <textarea name="description" id="description" rows="3" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white"></textarea>
                    @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label for="photo" class="block text-sm font-medium text-slate-300 mb-2">Foto Aset (Opsional)</label>
                    <input type="file" name="photo" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20 transition-all">
                    @error('photo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-10 flex justify-end gap-4">
                <a href="{{ route('jalan.index') }}" class="px-6 py-3 rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-600 font-medium transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 font-medium shadow-lg shadow-indigo-500/25 transition-all">
                    Simpan Aset Baru
                </button>
            </div>
        </form>
    </main>

</body>
</html>
