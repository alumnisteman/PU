<template>
  <div class="h-full flex flex-col bg-slate-950/90 backdrop-blur-2xl border-l border-slate-800/80 shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-800/60">
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
          <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_12px_#10b981]"></div>
          <h2 class="text-sm font-black tracking-widest uppercase text-slate-200">Intelligence Panel</h2>
        </div>
        <p class="text-[10px] text-slate-600 font-mono">Kab. Halmahera Selatan</p>
      </div>

      <!-- Meilisearch Search Bar -->
      <div class="relative group">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
          </svg>
        </div>
        <input 
          v-model="searchQuery" 
          @input="onSearchInput"
          type="search" 
          class="block w-full p-2.5 pl-10 text-xs text-white bg-slate-900/50 border border-slate-700 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-500 transition-all shadow-inner" 
          placeholder="Cari jalan (Meilisearch)..." 
          required 
        />
        <div v-if="isSearching" class="absolute inset-y-0 right-3 flex items-center">
            <div class="w-3 h-3 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3 p-4 border-b border-slate-800/60">
      <div class="bg-emerald-500/8 border border-emerald-500/20 p-3 rounded-xl">
        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Baik</p>
        <div class="text-2xl font-black text-emerald-400 leading-none">{{ stats.baik || 0 }}</div>
      </div>
      <div class="bg-amber-500/8 border border-amber-500/20 p-3 rounded-xl">
        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Sedang</p>
        <div class="text-2xl font-black text-amber-400 leading-none">{{ stats.sedang || 0 }}</div>
      </div>
      <div class="bg-orange-500/8 border border-orange-500/20 p-3 rounded-xl">
        <p class="text-[9px] font-black text-orange-600 uppercase tracking-widest mb-1">Rusak Ringan</p>
        <div class="text-2xl font-black text-orange-400 leading-none">{{ stats.rusak_ringan || 0 }}</div>
      </div>
      <div class="bg-red-500/8 border border-red-500/20 p-3 rounded-xl">
        <p class="text-[9px] font-black text-red-600 uppercase tracking-widest mb-1">Rusak Berat</p>
        <div class="text-2xl font-black text-red-400 leading-none">{{ stats.rusak_berat || 0 }}</div>
      </div>
    </div>

    <!-- Damage Reports / Search Results -->
    <div class="flex-grow overflow-hidden flex flex-col p-4">
      <h3 class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full animate-pulse" :class="searchQuery ? 'bg-indigo-500' : 'bg-red-500'"></span>
        {{ searchQuery ? 'Hasil Pencarian' : 'Kerusakan Terdeteksi' }}
      </h3>
      
      <div class="overflow-y-auto space-y-2.5 pr-1 flex-grow" style="-webkit-overflow-scrolling:touch;scrollbar-width:thin;scrollbar-color:#1e293b transparent">
        <div v-if="damageReports.length === 0" class="text-center py-8 text-slate-700 text-xs">
          {{ searchQuery ? 'Tidak ada jalan yang sesuai pencarian' : 'Belum ada data' }}
        </div>
        
        <div v-for="road in damageReports" :key="road.id"
             class="bg-slate-900/40 p-3.5 rounded-xl border transition-all cursor-pointer group"
             :class="road.condition === 'rusak_berat' ? 'border-red-900/30 hover:border-red-500/40' : 'border-slate-800 hover:border-indigo-500/40'">
          
          <div class="flex justify-between items-start mb-2">
            <span class="text-[9px] font-black uppercase tracking-tighter px-2 py-0.5 rounded-full"
                  :class="{
                    'text-emerald-400 bg-emerald-500/10': road.condition === 'baik',
                    'text-amber-400 bg-amber-500/10': road.condition === 'sedang',
                    'text-orange-400 bg-orange-500/10': road.condition === 'rusak_ringan',
                    'text-red-400 bg-red-500/10': road.condition === 'rusak_berat'
                  }">
              {{ road.condition.replace('_', ' ') }}
            </span>
            <span class="text-[9px] text-slate-700 font-mono">{{ road.code }}</span>
          </div>
          
          <h4 class="text-xs font-bold text-slate-300 leading-snug mb-2">{{ road.name }}</h4>
          
          <!-- Damage Details (if any) -->
          <div v-if="road.damage_type" class="mt-2 pt-2 border-t border-slate-800">
             <div class="text-[10px] font-bold text-red-400 mb-1">⚠️ {{ road.damage_type }}</div>
             <div class="grid grid-cols-2 gap-1.5">
               <div v-for="(val, key) in road.damage_details" :key="key" class="text-[9px]">
                 <span class="text-slate-600 capitalize">{{ key.replace(/_/g, ' ') }}:</span>
                 <span class="text-slate-300 font-bold ml-1">{{ val }}</span>
               </div>
             </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t border-slate-800/60 flex justify-between items-center bg-slate-950">
      <div class="flex items-center gap-2">
        <div class="w-1.5 h-1.5 rounded-full animate-pulse" :class="searchMode ? 'bg-indigo-500' : 'bg-emerald-500'"></div>
        <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest">{{ searchMode ? 'Meilisearch Active' : 'Live Sync' }}</span>
      </div>
      <span class="text-[9px] font-mono text-slate-800">SISMAP-PULSE v3.0</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const stats = ref({ baik: 0, sedang: 0, rusak_ringan: 0, rusak_berat: 0 });
const damageReports = ref([]);
const searchQuery = ref('');
const isSearching = ref(false);
let searchTimeout = null;
let pollInterval = null;

const searchMode = computed(() => searchQuery.value.length > 0);

async function loadData(query = '') {
  try {
    if(query) isSearching.value = true;
    
    const url = query ? `/api/roads/dashboard?q=${encodeURIComponent(query)}` : '/api/roads/dashboard';
    const res = await fetch(url);
    const data = await res.json();
    
    stats.value = data.condition_stats;
    
    // If searching, show all matching roads. If not, only show roads with damage
    if (query) {
       damageReports.value = data.damage_reports || [];
    } else {
       damageReports.value = (data.damage_reports || []).filter(r => r.damage_type);
    }
    
    isSearching.value = false;
  } catch (e) {
    console.error("Panel Error:", e);
    isSearching.value = false;
  }
}

function onSearchInput() {
  clearTimeout(searchTimeout);
  
  // Pause live polling while typing/searching
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
  
  searchTimeout = setTimeout(() => {
    loadData(searchQuery.value);
    
    // Resume polling if search is cleared
    if (!searchQuery.value) {
      startPolling();
    }
  }, 300); // 300ms debounce
}

function startPolling() {
  if (!pollInterval) {
    pollInterval = setInterval(() => {
      if (!searchQuery.value) loadData();
    }, 8000);
  }
}

onMounted(() => {
  loadData();
  startPolling();
});
</script>
