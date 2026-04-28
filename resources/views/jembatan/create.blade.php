<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jembatan Baru - Sismap</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans min-h-screen selection:bg-purple-500 selection:text-white">

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

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">Input Aset Jembatan Baru</h2>
            <p class="text-slate-400">Silakan lengkapi formulir di bawah untuk menambahkan data jembatan ke sistem.</p>
        </div>

        <form action="{{ route('jembatan.store') }}" method="POST" enctype="multipart/form-data" class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-[2rem] p-8 sm:p-12 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl -z-10"></div>
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                <!-- Location Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-400 border-b border-slate-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Lokasi & Wilayah
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="jembatan_propinsi_id" class="block text-sm font-medium text-slate-300 mb-2">Provinsi <span class="text-red-500">*</span></label>
                            <select name="jembatan_propinsi_id" id="jembatan_propinsi_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3.5 text-white transition-all">
                                <option value="">Pilih Provinsi</option>
                                @foreach($propinsis as $prop)
                                    <option value="{{ $prop->propinsi_id }}" {{ old('jembatan_propinsi_id') == $prop->propinsi_id ? 'selected' : '' }}>{{ $prop->propinsi_nama }}</option>
                                @endforeach
                            </select>
                            @error('jembatan_propinsi_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="jembatan_kota_id" class="block text-sm font-medium text-slate-300 mb-2">Kota / Kabupaten <span class="text-red-500">*</span></label>
                            <select name="jembatan_kota_id" id="jembatan_kota_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3.5 text-white transition-all">
                                <option value="">Pilih Kota</option>
                                <!-- Populated via AJAX -->
                            </select>
                            @error('jembatan_kota_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <script>
                    document.getElementById('jembatan_propinsi_id').addEventListener('change', function() {
                        const provinceId = this.value;
                        const citySelect = document.getElementById('jembatan_kota_id');
                        
                        citySelect.innerHTML = '<option value="">Memuat...</option>';
                        
                        if (!provinceId) {
                            citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                            return;
                        }

                        fetch(`/api/wilayah/cities/${provinceId}`)
                            .then(response => response.json())
                            .then(data => {
                                citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                                data.forEach(city => {
                                    const option = document.createElement('option');
                                    option.value = city.kota_id;
                                    option.textContent = city.kota_nama;
                                    citySelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching cities:', error);
                                citySelect.innerHTML = '<option value="">Gagal memuat data</option>';
                            });
                    });
                </script>

                <!-- Identity Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-pink-400 border-b border-slate-700 pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Spesifikasi Aset
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="jembatan_nama" class="block text-sm font-medium text-slate-300 mb-2">Nama Jembatan <span class="text-red-500">*</span></label>
                            <input type="text" name="jembatan_nama" id="jembatan_nama" value="{{ old('jembatan_nama') }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white placeholder-slate-500" placeholder="Contoh: Jembatan Ampera">
                            @error('jembatan_nama') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="jembatan_panjang" class="block text-sm font-medium text-slate-300 mb-2">Bentang (m) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="jembatan_panjang" id="jembatan_panjang" value="{{ old('jembatan_panjang') }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white">
                                @error('jembatan_panjang') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="jembatan_lebar" class="block text-sm font-medium text-slate-300 mb-2">Lebar (m) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="jembatan_lebar" id="jembatan_lebar" value="{{ old('jembatan_lebar') }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white">
                                @error('jembatan_lebar') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Group -->
            <div class="space-y-6 pt-10 border-t border-slate-700/50">
                <div>
                    <label for="jembatan_foto" class="block text-sm font-medium text-slate-300 mb-3">Foto Aset Jembatan</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="jembatan_foto" class="flex flex-col items-center justify-center w-full h-40 border-2 border-slate-700 border-dashed rounded-[1.5rem] cursor-pointer bg-slate-900/30 hover:bg-slate-900/50 transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mb-2 text-sm text-slate-400 font-semibold">Klik untuk upload foto</p>
                                <p class="text-xs text-slate-500 uppercase">PNG, JPG atau JPEG (Max. 2MB)</p>
                            </div>
                            <input id="jembatan_foto" name="jembatan_foto" type="file" class="hidden" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end gap-5">
                <a href="{{ route('jembatan.index') }}" class="px-8 py-3.5 rounded-2xl text-slate-400 hover:text-white font-bold transition-all">Batal</a>
                <button type="submit" class="px-10 py-3.5 rounded-2xl text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 font-bold shadow-xl shadow-purple-500/20 transition-all transform hover:scale-[1.02]">
                    Simpan Aset
                </button>
            </div>
        </form>
    </main>

</body>
</html>
