<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jembatan - {{ $jembatan->jembatan_nama }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans min-h-screen selection:bg-purple-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('jembatan.show', $jembatan->jembatan_id) }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-medium text-sm">Batal</span>
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">Si-DILAN</h1>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">Perbarui Data: {{ $jembatan->jembatan_nama }}</h2>
            <p class="text-slate-400">Modifikasi spesifikasi teknis dan detail aset jembatan.</p>
        </div>

        <form action="{{ route('jembatan.update', $jembatan->jembatan_id) }}" method="POST" enctype="multipart/form-data" class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-[2rem] p-8 sm:p-12 shadow-2xl relative">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                <!-- Location Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-purple-400 border-b border-slate-700 pb-3 flex items-center gap-2">
                        Wilayah
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="jembatan_propinsi_id" class="block text-sm font-medium text-slate-300 mb-2">Provinsi</label>
                            <select name="jembatan_propinsi_id" id="jembatan_propinsi_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3.5 text-white">
                                @foreach($propinsis as $prop)
                                    <option value="{{ $prop->propinsi_id }}" {{ $jembatan->jembatan_propinsi_id == $prop->propinsi_id ? 'selected' : '' }}>{{ $prop->propinsi_nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="jembatan_kota_id" class="block text-sm font-medium text-slate-300 mb-2">Kota / Kabupaten</label>
                            <select name="jembatan_kota_id" id="jembatan_kota_id" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-3.5 text-white">
                                @foreach($kotas as $kota)
                                    <option value="{{ $kota->kota_id }}" {{ $jembatan->jembatan_kota_id == $kota->id_kota ? 'selected' : '' }}>{{ $kota->kota_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Identity Group -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-pink-400 border-b border-slate-700 pb-3 flex items-center gap-2">
                        Spesifikasi
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="jembatan_nama" class="block text-sm font-medium text-slate-300 mb-2">Nama Jembatan</label>
                            <input type="text" name="jembatan_nama" id="jembatan_nama" value="{{ old('jembatan_nama', $jembatan->jembatan_nama) }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="jembatan_panjang" class="block text-sm font-medium text-slate-300 mb-2">Bentang (m)</label>
                                <input type="number" step="0.01" name="jembatan_panjang" id="jembatan_panjang" value="{{ old('jembatan_panjang', $jembatan->jembatan_panjang) }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white">
                            </div>
                            <div>
                                <label for="jembatan_lebar" class="block text-sm font-medium text-slate-300 mb-2">Lebar (m)</label>
                                <input type="number" step="0.01" name="jembatan_lebar" id="jembatan_lebar" value="{{ old('jembatan_lebar', $jembatan->jembatan_lebar) }}" required class="bg-slate-900/50 border border-slate-700 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-3.5 text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end gap-5">
                <button type="submit" class="px-12 py-4 rounded-2xl text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 font-bold shadow-xl shadow-purple-500/20 transition-all transform hover:scale-[1.02]">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </main>

</body>
</html>
