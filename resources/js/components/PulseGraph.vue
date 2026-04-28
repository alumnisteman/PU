<template>
  <div id="pulse-map" class="h-full w-full bg-slate-900 rounded-2xl border border-slate-700 shadow-inner overflow-hidden relative">
    <div v-if="loading" class="absolute inset-0 bg-slate-900/80 z-[1000] flex items-center justify-center backdrop-blur-sm">
      <div class="text-cyan-400 font-mono text-sm animate-pulse flex flex-col items-center gap-2">
        <svg class="animate-spin h-8 w-8 text-cyan-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>INITIALIZING PULSE NETWORK...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import { AntPath } from 'leaflet-ant-path';
import axios from 'axios';

let map;
const loading = ref(true);

// Fungsi untuk mendapatkan rute jalan sesungguhnya dari OSRM
async function getRouteGeometry(startLng, startLat, endLng, endLat) {
  try {
    // Gunakan OSRM public API untuk mendapatkan geometri jalan
    const url = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
    const response = await fetch(url, { signal: AbortSignal.timeout(5000) });
    if (!response.ok) throw new Error('OSRM request failed');
    const data = await response.json();
    
    if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
      // Koordinat dari OSRM adalah [lng, lat], konversi ke [lat, lng] untuk Leaflet
      return data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
    }
  } catch (e) {
    // Jika OSRM gagal (timeout/offline), gunakan koordinat asli
    console.warn('OSRM routing failed, using direct line:', e.message);
  }
  return null;
}

// Interpolasi titik-titik di sepanjang garis lurus (fallback)
function interpolatePoints(startLat, startLng, endLat, endLng, numPoints = 10) {
  const points = [];
  for (let i = 0; i <= numPoints; i++) {
    const t = i / numPoints;
    points.push([
      startLat + (endLat - startLat) * t,
      startLng + (endLng - startLng) * t
    ]);
  }
  return points;
}

async function loadGraph() {
  try {
    const res = await axios.get('/api/roads/dashboard');
    const data = res.data;

    let allLatLngs = [];

    // Proses setiap jalan secara paralel dengan batasan
    const roads = data.roads || [];
    const BATCH_SIZE = 5; // Proses 5 jalan sekaligus untuk menghindari rate limit

    for (let i = 0; i < roads.length; i += BATCH_SIZE) {
      const batch = roads.slice(i, i + BATCH_SIZE);
      
      await Promise.all(batch.map(async (r) => {
        let coordinates = null;

        // Ambil koordinat dari geometry (format GeoJSON dari API)
        if (r.geometry && r.geometry.coordinates && r.geometry.coordinates.length >= 2) {
          const geomCoords = r.geometry.coordinates;
          const startPoint = geomCoords[0];
          const endPoint = geomCoords[geomCoords.length - 1];

          // Jika hanya 2 titik, coba dapatkan rute jalan sesungguhnya via OSRM
          if (geomCoords.length === 2) {
            const routeCoords = await getRouteGeometry(
              startPoint[0], startPoint[1],  // [lng, lat]
              endPoint[0], endPoint[1]
            );
            
            if (routeCoords && routeCoords.length > 2) {
              coordinates = routeCoords; // [lat, lng] dari OSRM
            } else {
              // Fallback: interpolasi titik-titik di sepanjang segmen
              coordinates = interpolatePoints(
                startPoint[1], startPoint[0],  // lat, lng
                endPoint[1], endPoint[0]
              );
            }
          } else {
            // Sudah ada banyak titik, konversi [lng,lat] -> [lat,lng]
            coordinates = geomCoords.map(c => [c[1], c[0]]);
          }
        } else if (r.geom && r.geom.coordinates && r.geom.coordinates.length >= 2) {
          // Fallback ke field geom
          const geomCoords = r.geom.coordinates;
          const startPoint = geomCoords[0];
          const endPoint = geomCoords[geomCoords.length - 1];
          
          if (geomCoords.length === 2) {
            const routeCoords = await getRouteGeometry(
              startPoint[0], startPoint[1],
              endPoint[0], endPoint[1]
            );
            coordinates = routeCoords || interpolatePoints(
              startPoint[1], startPoint[0],
              endPoint[1], endPoint[0]
            );
          } else {
            coordinates = geomCoords.map(c => [c[1], c[0]]);
          }
        }

        if (coordinates && coordinates.length > 0) {
          allLatLngs.push(...coordinates);

          // Warna premium berdasarkan kondisi
          let color = '#10b981'; // Neon Green (baik)
          let glowClass = 'glow-baik';
          if (r.condition === 'sedang') { color = '#facc15'; glowClass = 'glow-sedang'; }
          if (r.condition === 'rusak_ringan') { color = '#f97316'; glowClass = 'glow-rusak-ringan'; }
          if (r.condition === 'rusak_berat') { color = '#ef4444'; glowClass = 'glow-rusak-berat'; }

          // Buat AntPath dengan koordinat jalan sesungguhnya
          const path = new AntPath(coordinates, {
            delay: 400,
            dashArray: [15, 30],
            weight: r.condition === 'rusak_berat' ? 5 : 3,
            color: '#1e293b',
            pulseColor: color,
            paused: false,
            reverse: false,
            hardwareAccelerated: true,
            className: `premium-pulse ${glowClass}`
          });

          // Tooltip premium
          path.bindTooltip(`
            <div class="bg-slate-800 text-white p-2 rounded border border-slate-600 shadow-xl font-mono text-xs">
              <div class="text-slate-400 mb-1">ID: ${r.code}</div>
              <div class="font-bold text-sm text-cyan-400">${r.name}</div>
              <div class="text-slate-400 text-[10px] mt-1">${r.length_km} KM</div>
              <div class="mt-1 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full inline-block" style="background-color: ${color}; box-shadow: 0 0 5px ${color}"></span>
                <span class="uppercase tracking-wider">${(r.condition || '').replace('_', ' ')}</span>
              </div>
            </div>
          `, { sticky: true, className: 'custom-tooltip' });

          path.addTo(map);
        }
      }));

      // Delay kecil antara batch untuk menghindari rate limit OSRM
      if (i + BATCH_SIZE < roads.length) {
        await new Promise(resolve => setTimeout(resolve, 300));
      }
    }

    if (allLatLngs.length > 0) {
      map.fitBounds(L.latLngBounds(allLatLngs), { padding: [20, 20], animate: true, duration: 2 });
    }

  } catch (e) {
    console.error("Pulse Map Error", e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  map = L.map('pulse-map', {
    zoomControl: false,
    attributionControl: false,
    dragging: true,
    scrollWheelZoom: true,
    doubleClickZoom: false,
    boxZoom: false,
    keyboard: false
  }).setView([-0.6300, 127.4800], 8);

  // Basemap gelap premium
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd',
    maxZoom: 20
  }).addTo(map);

  loadGraph();
});
</script>

<style>
/* Premium Glow Effects */
.premium-pulse {
  stroke-linecap: round;
  stroke-linejoin: round;
  transition: filter 0.3s ease;
}

.glow-baik { filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.9)); }
.glow-sedang { filter: drop-shadow(0 0 6px rgba(250, 204, 21, 0.9)); }
.glow-rusak-ringan { filter: drop-shadow(0 0 6px rgba(249, 115, 22, 0.9)); }
.glow-rusak-berat { filter: drop-shadow(0 0 6px rgba(239, 68, 68, 0.9)); }

/* Custom Tooltip */
.leaflet-tooltip.custom-tooltip {
  background: transparent;
  border: none;
  box-shadow: none;
  padding: 0;
}
.leaflet-tooltip.custom-tooltip::before {
  display: none;
}
</style>
