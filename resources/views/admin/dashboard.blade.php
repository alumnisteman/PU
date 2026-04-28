<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISMAP Modern | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-900 text-white min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 border-r border-slate-800 h-screen sticky top-0 p-6 flex flex-col gap-8">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V6.418a2 2 0 011.106-1.789L9 2l5 2.5L20 2v9m-9 9l5-2.5L20 11m-9 9V9l5-2.5L20 11m-9 9v-11"></path></svg>
                </div>
                <span class="font-bold text-lg tracking-tighter">ADMIN PORTAL</span>
            </div>
            
            <nav class="space-y-2">
                <a href="/admin" class="flex items-center gap-3 p-3 rounded-xl bg-blue-600/10 text-blue-500 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="/admin/jalan" class="flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 20l-5-2.5V6l5 2.5L14 6l5 2.5V18l-5-2.5L9 20z"></path></svg>
                    Data Jalan
                </a>
                <a href="/admin/jembatan" class="flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 20l-5-2.5V6l5 2.5L14 6l5 2.5V18l-5-2.5L9 20z"></path></svg>
                    Data Jembatan
                </a>
                <div class="pt-4 border-t border-slate-800 mt-4">
                    <a href="/" class="flex items-center gap-3 p-3 rounded-xl text-emerald-500 hover:bg-emerald-500/10 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Public Pulse
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-black mb-2">Management Portal</h1>
                    <p class="text-slate-400 font-medium">Update Infrastruktur & Damage Reports Halmahera Selatan.</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/admin/jalan/create" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/20 transition-all">
                        + Tambah Data
                    </a>
                </div>
            </header>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-slate-800/40 border border-slate-700 p-6 rounded-2xl">
                    <p class="text-slate-500 text-xs font-bold mb-2 uppercase tracking-widest">Total KM</p>
                    <h2 class="text-3xl font-black" id="total_km">0 KM</h2>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/20 p-6 rounded-2xl">
                    <p class="text-emerald-500 text-xs font-bold mb-2 uppercase tracking-widest">Baik</p>
                    <h2 class="text-3xl font-black text-emerald-500" id="count_baik">0</h2>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/20 p-6 rounded-2xl">
                    <p class="text-amber-500 text-xs font-bold mb-2 uppercase tracking-widest">Sedang</p>
                    <h2 class="text-3xl font-black text-amber-400" id="count_sedang">0</h2>
                </div>
                <div class="bg-rose-500/10 border border-rose-500/20 p-6 rounded-2xl">
                    <p class="text-rose-500 text-xs font-bold mb-2 uppercase tracking-widest">Rusak</p>
                    <h2 class="text-3xl font-black text-rose-500" id="count_rusak">0</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Chart -->
                <div class="lg:col-span-1 bg-slate-800/40 border border-slate-700 p-8 rounded-2xl">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-8">Kondisi Jalan (%)</h3>
                    <canvas id="conditionChart"></canvas>
                </div>

                <!-- Priority Table -->
                <div class="lg:col-span-2 bg-slate-800/40 border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
                    <div class="p-6 border-b border-slate-700 flex items-center justify-between bg-slate-800/60">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-200">Antrian Perbaikan</h3>
                        <span class="px-3 py-1 bg-blue-600/20 text-blue-500 text-[10px] font-black rounded-full uppercase tracking-tighter">Smart Analysis</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-900/40 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                                <tr>
                                    <th class="p-6">Ruas Jalan</th>
                                    <th class="p-6">Kondisi</th>
                                    <th class="p-6">Prioritas</th>
                                    <th class="p-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="priority_table" class="text-sm">
                                <!-- Data injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        fetch('/api/roads/dashboard')
            .then(res => res.json())
            .then(data => {
                document.getElementById('total_km').innerText = data.total_km + ' KM';
                document.getElementById('count_baik').innerText = data.condition_stats.baik;
                document.getElementById('count_sedang').innerText = data.condition_stats.sedang;
                document.getElementById('count_rusak').innerText = data.condition_stats.rusak;

                const table = document.getElementById('priority_table');
                data.priority_roads.forEach(road => {
                    const statusColor = road.condition === 'baik' ? 'text-emerald-500' : (road.condition === 'sedang' ? 'text-amber-500' : 'text-rose-500');
                    table.innerHTML += `
                        <tr class="border-b border-slate-800/50 hover:bg-slate-800/40 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-slate-200">${road.name}</div>
                                <div class="text-[10px] font-mono text-slate-600 mt-1">${road.code}</div>
                            </td>
                            <td class="p-6">
                                <span class="px-2 py-1 bg-slate-900/60 rounded text-[10px] font-black uppercase ${statusColor}">
                                    ${road.condition.replace('_', ' ')}
                                </span>
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-1.5 bg-slate-900 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-600" style="width: ${Math.min(road.id * 10, 100)}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-slate-400">${Math.min(road.id * 10, 100)}</span>
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="/admin/jalan/${road.id}/edit" class="text-slate-400 hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <a href="/admin/jalan/${road.id}" class="text-blue-500 hover:text-blue-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                new Chart(document.getElementById('conditionChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Baik', 'Sedang', 'Rusak'],
                        datasets: [{
                            data: [data.condition_stats.baik, data.condition_stats.sedang, data.condition_stats.rusak],
                            backgroundColor: ['#10b981', '#f59e0b', '#e11d48'],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        cutout: '75%',
                        plugins: {
                            legend: { display: true, position: 'bottom', labels: { color: '#94a3b8', font: { weight: 'bold', size: 10 } } }
                        }
                    }
                });
            });
    </script>
</body>
</html>
