<template>
  <div id="map" class="h-full w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-700/50"></div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet-ant-path';
import 'leaflet.heat';
import Pusher from 'pusher-js';
import axios from 'axios';

let map;
let markers = {};
let roadLayers = [];
let heatLayer = null;
let pusher = null;

const getColor = (c) => {
  const map = {
    'baik': '#10b981',
    'sedang': '#f59e0b',
    'rusak_ringan': '#f97316',
    'rusak_berat': '#e11d48'
  };
  return map[c] || '#e11d48';
};

const workerIcon = L.divIcon({
  className: 'gps-icon',
  html: `<div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-[0_0_10px_rgba(59,130,246,0.8)] animate-pulse"></div>`,
  iconSize: [16, 16],
  iconAnchor: [8, 8]
});

async function loadData() {
  try {
    const res = await axios.get('/api/roads/dashboard');
    const data = res.data;

    // Clear old layers
    roadLayers.forEach(layer => map.removeLayer(layer));
    roadLayers = [];

    // 1. Render Roads with Ant-Path
    if (data.priority_roads) {
      data.priority_roads.forEach(r => {
        if (!r.lat || !r.lng) return;
        
        // Mock geometry if not present (using single point for demo)
        const latlngs = [[r.lat, r.lng], [r.lat + 0.002, r.lng + 0.002]];
        
        const color = getColor(r.condition);
        const path = L.polyline.antPath(latlngs, {
          color: color,
          pulseColor: '#ffffff',
          delay: 1000,
          weight: 5
        }).addTo(map);

        path.bindPopup(`<b>${r.name}</b><br>Kondisi: ${r.condition}<br>Skor: ${r.priority_score}`);
        roadLayers.push(path);
      });

      // 2. Update Heatmap
      const heatPoints = data.priority_roads
        .filter(r => r.condition === 'rusak_berat')
        .map(r => [r.lat, r.lng, 1.0]);
      
      if (heatLayer) heatLayer.setLatLngs(heatPoints);
    }

    // 3. Initial Workers
    if (data.users) {
      data.users.forEach(updateWorkerMarker);
    }
  } catch (err) {
    console.error('Failed to load map data:', err);
  }
}

function updateWorkerMarker(worker) {
  if (!worker.lat || !worker.lng) return;
  
  if (markers[worker.id]) {
    markers[worker.id].setLatLng([worker.lat, worker.lng]);
  } else {
    markers[worker.id] = L.marker([worker.lat, worker.lng], { icon: workerIcon })
      .addTo(map)
      .bindPopup(`<b>Petugas: ${worker.name}</b>`);
  }
}

function initPusher() {
  pusher = new Pusher('key', {
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
  });

  const channel = pusher.subscribe('workers');
  channel.bind('App\\Events\\WorkerLocationUpdated', (data) => {
    updateWorkerMarker(data.worker);
  });
}

onMounted(() => {
  map = L.map('map', { zoomControl: false }).setView([-0.7893, 127.3750], 12);
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
  
  heatLayer = L.heatLayer([], { radius: 25, blur: 15 }).addTo(map);
  
  initPusher();
  loadData();
  setInterval(loadData, 30000); // Poll for road data every 30s
});

onUnmounted(() => {
  if (pusher) pusher.disconnect();
});
</script>

<style>
.gps-icon {
  background: transparent;
  border: none;
}
</style>
