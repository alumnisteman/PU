<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aset Jalan - Sismap</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
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

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-white mb-2">Ubah Data Aset: {{ $jalan->road_name }}</h2>
            <p class="text-slate-400">Perbarui spesifikasi dan informasi aset jalan yang sudah ada.</p>
        </div>

        <form action="{{ route('jalan.update', $jalan->id) }}" method="POST" enctype="multipart/form-data" class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-3xl p-8 sm:p-10 shadow-2xl">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Location Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-indigo-400 border-b border-slate-700 pb-2">Informasi Wilayah</h3>
                    
                    <div>
                        <label for="region_id" class="block text-sm font-medium text-slate-300 mb-2">Wilayah / Regional <span class="text-red-500">*</span></label>
                        <select name="region_id" id="region_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white">
                            <option value="">Pilih Wilayah</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ $jalan->region_id == $region->id ? 'selected' : '' }}>
                                    {{ $region->province }} - {{ $region->city }} - {{ $region->district }}
                                </option>
                            @endforeach
                        </select>
                        @error('region_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-slate-300 mb-2">Latitude</label>
                            <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $jalan->latitude) }}" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500" placeholder="Contoh: -0.79012">
                            @error('latitude') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-slate-300 mb-2">Longitude</label>
                            <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $jalan->longitude) }}" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500" placeholder="Contoh: 127.38411">
                            @error('longitude') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="elevation" class="block text-sm font-medium text-slate-300 mb-2">Elevasi (mdpl)</label>
                        <input type="number" step="0.01" name="elevation" id="elevation" value="{{ old('elevation', $jalan->elevation) }}" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white placeholder-slate-500">
                        @error('elevation') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Asset Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-400 border-b border-slate-700 pb-2">Identitas Aset</h3>
                    
                    <div>
                        <label for="road_code" class="block text-sm font-medium text-slate-300 mb-2">Kode Ruas Jalan</label>
                        <input type="text" name="road_code" id="road_code" value="{{ old('road_code', $jalan->road_code) }}" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white" placeholder="Opsi">
                        @error('road_code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="road_name" class="block text-sm font-medium text-slate-300 mb-2">Nama Ruas Jalan <span class="text-red-500">*</span></label>
                        <input type="text" name="road_name" id="road_name" value="{{ old('road_name', $jalan->road_name) }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white" placeholder="Nama Jalan">
                        @error('road_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="length_km" class="block text-sm font-medium text-slate-300 mb-2">Panjang (km) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="length_km" id="length_km" value="{{ old('length_km', $jalan->length_km) }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            @error('length_km') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="width_m" class="block text-sm font-medium text-slate-300 mb-2">Lebar (m)</label>
                            <input type="number" step="0.01" name="width_m" id="width_m" value="{{ old('width_m', $jalan->width_m) }}" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            @error('width_m') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="condition_status" class="block text-sm font-medium text-slate-300 mb-2">Status Kondisi</label>
                        <select name="condition_status" id="condition_status" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3 text-white">
                            <option value="baik" {{ $jalan->condition_status == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="sedang" {{ $jalan->condition_status == 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="rusak_ringan" {{ $jalan->condition_status == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="rusak_berat" {{ $jalan->condition_status == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        @error('condition_status') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Group -->
            <div class="space-y-6 pt-6 border-t border-slate-700/50">
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-300 mb-2">Keterangan Tambahan</label>
                    <textarea name="description" id="description" rows="3" class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3 text-white">{{ old('description', $jalan->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <label for="photo" class="block text-sm font-medium text-slate-300 mb-2">Pembaruan Foto Aset</label>
                        <input type="file" name="photo" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20 transition-all">
                        <p class="text-slate-500 text-xs mt-2">Biarkan kosong jika tidak ingin mengubah foto aset.</p>
                        @error('photo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @if($jalan->photo_url)
                    <div class="flex justify-end">
                        <div class="w-32 h-32 rounded-xl overflow-hidden border border-slate-600 bg-slate-900">
                            <img src="{{ url('uploads/others/pu/' . \Carbon\Carbon::parse($jalan->created_at)->format('Y') . '/' . $jalan->photo_url) }}" alt="Foto Aset" class="w-full h-full object-cover">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-10 flex justify-end gap-4">
                <a href="{{ route('jalan.index') }}" class="px-6 py-3 rounded-xl text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-600 font-medium transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 font-medium shadow-lg shadow-indigo-500/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <!-- Cost Estimation Section -->
        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-3xl p-8 sm:p-10 shadow-2xl mt-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-white">Estimasi Biaya Material</h3>
                    <p class="text-slate-400 text-sm">Hitung perkiraan biaya perbaikan/pembangunan ruas jalan ini.</p>
                </div>
                <div class="px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <span class="text-xs font-bold text-emerald-500 uppercase tracking-widest block">Total Estimasi</span>
                    <span class="text-xl font-black text-white">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-500 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif

            <!-- Material Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-700/50 mb-8">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/50 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Material</th>
                            <th class="px-6 py-4">Volume</th>
                            <th class="px-6 py-4">Harga Satuan</th>
                            <th class="px-6 py-4">Subtotal</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/30">
                        @forelse($roadMaterials as $rm)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-200">{{ $rm->material->name }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $rm->volume }} {{ $rm->material->unit }}</td>
                            <td class="px-6 py-4 text-slate-400">Rp {{ number_format($rm->material->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-indigo-400">Rp {{ number_format($rm->volume * $rm->material->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('road_material.remove', $rm->id) }}" method="POST" onsubmit="return confirm('Hapus material ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">Belum ada material yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Add Material Form -->
                <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-700/50">
                    <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        Tambah Kebutuhan Material
                    </h4>
                    <form action="{{ route('road_material.add') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="road_id" value="{{ $jalan->id }}">
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block">Pilih Material</label>
                            <select name="material_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-sm text-white">
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block">Volume (Kuantitas)</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" name="volume" id="input_volume" required class="flex-1 bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-sm text-white" placeholder="0.00">
                                <button type="button" onclick="autoCalc()" class="px-3 py-2 bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 rounded-xl text-xs font-bold hover:bg-indigo-500/20 transition-all">Auto</button>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/20">
                            Tambah ke Estimasi
                        </button>
                    </form>
                </div>

                <!-- Update Price Form -->
                <div class="bg-slate-900/40 p-6 rounded-2xl border border-slate-700/50">
                    <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" /></svg>
                        Update Harga Material (Satuan)
                    </h4>
                    <form action="{{ route('material.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block">Material</label>
                            <select name="id" id="price_material_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-sm text-white" onchange="updatePriceInput()">
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}" data-price="{{ $mat->price }}">{{ $mat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block">Harga Baru (Rp)</label>
                            <input type="number" name="price" id="price_input" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-sm text-white" placeholder="0">
                        </div>
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-500/20">
                            Update Harga
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updatePriceInput() {
            const sel = document.getElementById('price_material_id');
            const price = sel.options[sel.selectedIndex].getAttribute('data-price');
            document.getElementById('price_input').value = price;
        }
        updatePriceInput();

        function autoCalc() {
            const length = parseFloat(document.getElementById('length_km').value) || 0;
            const width = parseFloat(document.getElementById('width_m').value) || 0;
            const volumeInput = document.getElementById('input_volume');
            
            // Logic: length(m) * width(m) * thickness(0.05m)
            // length is in km, so multiply by 1000
            const volume = length * 1000 * width * 0.05;
            volumeInput.value = volume.toFixed(2);
        }

        // --- Realtime Sync ---
        const socket = io('http://' + window.location.hostname + ':3000');
        const currentAssetId = {{ $jalan->id }};
        
        socket.on('connect', () => {
            console.log('[Admin Edit] Socket connected for sync');
        });

        socket.on('road-selected', (payload) => {
            const selectedId = payload.asset_id || payload.id;
            // Jika diklik jalan lain dari map, auto redirect ke form edit jalan tersebut
            if (selectedId && selectedId != currentAssetId) {
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 right-4 bg-indigo-600/90 text-white px-6 py-3 rounded-xl shadow-2xl z-50 text-sm font-bold animate-pulse';
                toast.innerText = '⚡ Pindah edit ke jalan: ' + (payload.name || selectedId);
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    window.location.href = `/admin/jalan/${selectedId}/edit`;
                }, 1000);
            }
        });

        socket.on('data-refresh', (payload) => {
            const assetId = payload.asset_id || payload.id;
            if (assetId == currentAssetId && payload.condition) {
                // Update dropdown kondisi jika ada perubahan dari peta
                const select = document.getElementById('condition_status');
                if (select) {
                    // map values 'rusak_ringan', 'rusak_berat' back to 'rusak' for this form
                    let formValue = payload.condition.toLowerCase();
                    if (formValue.includes('rusak')) formValue = 'rusak';
                    
                    if (select.value !== formValue) {
                        select.value = formValue;
                        select.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-500/10');
                        setTimeout(() => select.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-500/10'), 2000);
                    }
                }
            }
        });
    </script>

</body>
</html>
