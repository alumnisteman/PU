<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Sismap</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-200 antialiased font-sans flex flex-col min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-slate-800/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">Si-DILAN</h1>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Beranda</a>
                <a href="{{ route('jalan.index') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Aset Jalan</a>
                <a href="{{ route('jembatan.index') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Aset Jembatan</a>
                <a href="{{ route('users.index') }}" class="text-sm font-bold text-white transition-colors border-b-2 border-indigo-500 pb-1">Pengguna</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-white tracking-tight">Daftar Pengguna Sistem</h2>
        </div>

        <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 rounded-3xl overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">Nama Lengkap</th>
                        <th class="px-6 py-4 font-bold">Email</th>
                        <th class="px-6 py-4 font-bold">Username</th>
                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 text-white font-medium">{{ $user->user_fullname }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $user->user_email }}</td>
                            <td class="px-6 py-4 text-slate-400 font-mono text-xs">{{ $user->user_name }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('users.show', $user->user_id) }}" class="text-indigo-400 hover:text-white text-sm font-bold transition-colors">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </main>

</body>
</html>
