
<template>
  <div id="map" class="h-full w-full min-h-[500px] rounded-2xl overflow-hidden shadow-2xl border border-slate-700/50"></div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-ant-path';
import * as turf from '@turf/turf';
import axios from 'axios';
import { io } from 'socket.io-client';

let map;
let markers = {};
let history = [];
let roadGeoJSON = null;
let roadLayers = {};   // { segmentId: antPathLayer }  — keyed by road/segment ID
let assetLayerMap = {}; // { assetId: [segmentId, ...] } — asset ke segments
let socket;
let damagedTourIndex = 0;
let damagedTourInterval = null;
let damagedRoads = [];

// ─── WARNA KONDISI ──────────────────────────────────────────────
function getColor(c) {
  const cond = (c || '').toLowerCase();
  if (cond === 'baik')        return '#10b981'; // emerald
  if (cond === 'sedang')      return '#f59e0b'; // amber
  if (cond === 'rusak_ringan') return '#f97316'; // orange
  if (cond === 'rusak' || cond === 'rusak_berat') return '#e11d48'; // rose-red
  return '#64748b'; // slate (unknown)
}

function getWeight(c) {
  const cond = (c || '').toLowerCase();
  if (cond === 'rusak' || cond === 'rusak_berat') return 10;
  if (cond === 'rusak_ringan') return 7;
  return 5;
}

function isDamaged(c) {
  return ['rusak', 'rusak_berat', 'rusak_ringan'].includes((c || '').toLowerCase());
}

// ─── LABEL KONDISI ───────────────────────────────────────────────
function labelKondisi(c) {
  const map = {
    baik: 'Baik', sedang: 'Sedang',
    rusak_ringan: 'Rusak Ringan',
    rusak_berat: 'Rusak Berat',
    rusak: 'Rusak',
  };
  return map[(c || '').toLowerCase()] || c;
}

// ─── OSRM (route geometry) ────────────────────────────────────────
async function getOSRMRoute(startLng, startLat, endLng, endLat) {
  try {
    const url = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
    const response = await fetch(url, { signal: AbortSignal.timeout(5000) });
    if (!response.ok) throw new Error('OSRM failed');
    const data = await response.json();
    if (data.code === 'Ok' && data.routes?.length > 0) {
      return data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
    }
  } catch (e) {
    console.warn('OSRM unavailable, pakai straight-line');
  }
  return null;
}

function interpolateLine(startLat, startLng, endLat, endLng, numPoints = 8) {
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

// ─── BUAT POPUP HTML ─────────────────────────────────────────────
function buildPopupHtml(r, color) {
  const assetId = r.asset_id; // DO NOT fallback to r.id
  const segId   = r.id;
  const cond    = r.condition || r.properties?.condition || 'baik';

  return `
    <div style="font-family:Inter,sans-serif;padding:6px;min-width:220px;color:#e2e8f0">
      ${r.photo_url ? `<img src="${r.photo_url}" style="width:100%;height:90px;object-fit:cover;border-radius:8px;margin-bottom:8px;border:1px solid #334155"/>` : ''}
      <div style="font-size:10px;color:#94a3b8;margin-bottom:2px;font-family:monospace">${r.code || ''}</div>
      <div style="font-weight:900;font-size:14px;color:white;margin-bottom:6px">${r.name}</div>
      <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px">
        <span style="width:8px;height:8px;border-radius:50%;background:${color};box-shadow:0 0 6px ${color};display:inline-block"></span>
        <span style="font-size:11px;text-transform:uppercase;color:${color};font-weight:700">${labelKondisi(cond)}</span>
        <span style="font-size:11px;color:#64748b;margin-left:auto">${r.length_km || (r.length_m ? (r.length_m / 1000).toFixed(2) : '-')} KM</span>
      </div>

      ${assetId ? `
      <!-- Quick update kondisi (map → admin) -->
      <div style="background:#0f172a;border-radius:10px;padding:10px;border:1px solid #1e293b;margin-bottom:8px">
        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;margin-bottom:6px">⚡ Update Kondisi</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          ${['baik','sedang','rusak_ringan','rusak_berat'].map(k => `
            <button
              onclick="window.__sismapUpdateCondition(${assetId}, '${segId}', '${k}')"
              style="
                flex:1;min-width:calc(50% - 3px);padding:5px 4px;border-radius:7px;border:1px solid ${getColor(k)}40;
                background:${k === cond ? getColor(k) + '30' : 'transparent'};
                color:${getColor(k)};font-size:10px;font-weight:700;cursor:pointer;
                outline:${k === cond ? '1.5px solid ' + getColor(k) : 'none'};
                transition:all .2s
              "
            >${labelKondisi(k)}</button>
          `).join('')}
        </div>
        <div id="popup-status-${segId}" style="font-size:10px;color:#94a3b8;margin-top:6px;text-align:center"></div>
      </div>

      <div style="padding-top:6px;border-top:1px solid #1e293b;display:flex;gap:8px;align-items:center">
        <a href="/admin/jalan/${assetId}/edit" target="_blank"
           style="flex:1;color:#6366f1;text-decoration:none;font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;gap:4px;padding:5px;background:#6366f110;border-radius:7px">
          <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Ubah Data Admin
        </a>
        <a href="/admin/jalan/${assetId}" target="_blank"
           style="flex:1;color:#10b981;text-decoration:none;font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;gap:4px;padding:5px;background:#10b98110;border-radius:7px">
          Detail
        </a>
      </div>
      ` : `
      <div style="background:#33415540;border-radius:10px;padding:10px;text-align:center;border:1px dashed #475569;">
        <div style="font-size:10px;color:#94a3b8;font-weight:700;">⚠ BELUM TERDATA DI ADMIN</div>
        <div style="font-size:9px;color:#64748b;margin-top:4px;">Tambahkan jalan ini di menu Admin untuk dapat mengubah kondisi dan detailnya.</div>
      </div>
      `}
    </div>
  `;
}

// ─── UPDATE LAYER WARNA (tanpa reload penuh) ──────────────────────
function updateLayerColor(segId, newCondition) {
  const layer = roadLayers[segId];
  if (!layer) return;
  const color  = getColor(newCondition);
  const weight = getWeight(newCondition);
  layer.setStyle({ color, weight, pulseColor: isDamaged(newCondition) ? '#fff' : '#00f0ff' });
  layer.options.condition = newCondition;
  // Rebuild popup content
  if (layer._roadData) {
    layer._roadData.condition = newCondition;
    layer.setPopupContent(buildPopupHtml(layer._roadData, color));
  }
}

// ─── TOAST NOTIFICATION (user-friendly error display) ────────────
function showToast(msg, type = 'warning', duration = 4000) {
  const id = 'sismap-toast-' + Date.now();
  const bg = type === 'error' ? '#e11d48' : type === 'success' ? '#10b981' : '#f59e0b';
  const toast = document.createElement('div');
  toast.id = id;
  toast.style.cssText = `position:fixed;bottom:20px;left:50%;transform:translateX(-50%);
    background:${bg};color:white;padding:10px 20px;border-radius:12px;z-index:99999;
    font-family:Inter,sans-serif;font-size:13px;font-weight:600;
    box-shadow:0 4px 20px rgba(0,0,0,0.4);pointer-events:none;
    animation:fadeInUp 0.3s ease`;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

// ─── LOAD DATA (road segments dari API) ──────────────────────────
async function loadData() {
  try {
    const res  = await fetch('/api/roads/dashboard');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    if (data.users) {
      data.users.forEach(u => updateMarker(u));
    }
    if (data.damaged_roads) {
      damagedRoads = data.damaged_roads;
    }
    // Roads/segments di-load via loadSegments() secara terpisah (map bounds)
  } catch (err) {
    console.error('Load dashboard error:', err);
    showToast('⚠ Gagal memuat data dashboard: ' + err.message, 'error');
  }
}

// ─── LOAD SEGMENTS (dipanggil saat map bergerak) ──────────────────
async function loadSegments() {
  if (!map) return;
  
  // Prevent loading thousands of segments when zoomed out too far (Safety)
  if (map.getZoom() < 11) {
    Object.keys(roadLayers).forEach(id => {
      map.removeLayer(roadLayers[id]);
      delete roadLayers[id];
    });
    return;
  }

  const bounds = map.getBounds();
  const bbox   = `${bounds.getWest()},${bounds.getSouth()},${bounds.getEast()},${bounds.getNorth()}`;

  try {
    // OPTIMASI MOBILE: Batasi jumlah segmen di layar kecil agar tidak nge-lag
    const isMobile = window.innerWidth <= 768;
    const limit = isMobile ? 800 : 5000;
    
    const res = await fetch(`/api/segments?bbox=${bbox}&limit=${limit}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const geojson = await res.json();
    if (geojson.error) {
      console.warn('[SISMAP] API returned error flag, retrying later...');
      return;
    }
    if (!geojson.features) return;

    const newIds = new Set();
    const allFeatures = [];

    await Promise.all(geojson.features.map(async (feature) => {
      const props   = feature.properties;
      const segId   = props.id || feature.id;
      const assetId = props.asset_id;
      newIds.add(segId);

      // Jika layer sudah ada — hanya update warna
      if (roadLayers[segId]) {
        updateLayerColor(segId, props.condition);
        return;
      }

      // Build latlngs dari geometry
      const geomCoords = feature.geometry?.coordinates;
      if (!geomCoords || geomCoords.length === 0) return;

      let latlngs;
      if (geomCoords.length === 2) {
        const [startPt, endPt] = geomCoords;
        const route = await getOSRMRoute(startPt[0], startPt[1], endPt[0], endPt[1]);
        latlngs = route || interpolateLine(startPt[1], startPt[0], endPt[1], endPt[0]);
      } else {
        latlngs = geomCoords.map(c => [c[1], c[0]]);
      }

      const condition = props.condition || 'baik';
      const color     = getColor(condition);
      const weight    = getWeight(condition);

      const roadData = {
        id:         segId,
        asset_id:   assetId,
        name:       props.name || 'Jalan',
        code:       props.highway || '',
        condition:  condition,
        length_km:  props.length_m ? (props.length_m / 1000).toFixed(2) : '-',
        length_m:   props.length_m,
        photo_url:  props.photo_url,
      };

      const isRoadDamaged = isDamaged(condition);

      let roadLayer;
      
      // OPTIMASI MOBILE: Gunakan antPath HANYA untuk jalan rusak. 
      // Jalan 'baik' gunakan L.polyline biasa agar dirender di Canvas (sangat ringan).
      if (isRoadDamaged) {
        roadLayer = L.polyline.antPath(latlngs, {
          color,
          weight,
          delay: 600,
          dashArray: [10, 20],
          pulseColor: '#ffffff',
          opacity: 0.9,
          className: 'glow-line',
          roadId: segId,
          assetId: assetId,
          condition: condition,
        });
      } else {
        roadLayer = L.polyline(latlngs, {
          color,
          weight,
          opacity: 0.7,
          roadId: segId,
          assetId: assetId,
          condition: condition,
        });
      }

      roadLayer._roadData = roadData;
      roadLayer._currentColor = color;

      // OPTIMASI MOBILE: Lazy Popup (Hanya render DOM HTML saat jalan diklik)
      roadLayer.on('click', () => {
        if (!roadLayer.getPopup()) {
           roadLayer.bindPopup(buildPopupHtml(roadLayer._roadData, roadLayer._currentColor), { maxWidth: 260 });
        }
        
        map.fitBounds(roadLayer.getBounds(), { padding: [150, 150], maxZoom: 17 });
        roadLayer.openPopup();

        // Beritahu server bahwa jalan ini di-klik di map
        if (socket?.connected) {
          socket.emit('road-selected', {
            id:        segId,
            asset_id:  assetId,
            name:      roadData.name,
            condition: roadData.condition,
            lat:       latlngs[0]?.[0],
            lng:       latlngs[0]?.[1],
          });
        }
      });

      roadLayer.addTo(map);
      roadLayers[segId] = roadLayer;
      if (assetId) {
        assetLayerMap[assetId] = assetLayerMap[assetId] || [];
        if (!assetLayerMap[assetId].includes(segId)) {
          assetLayerMap[assetId].push(segId);
        }
      }

      allFeatures.push({
        type: 'Feature',
        geometry: {
          type:        'LineString',
          coordinates: latlngs.map(pt => [pt[1], pt[0]])
        },
        properties: {}
      });
    }));

    // Update snapping GeoJSON (Replace instead of growing infinitely to save memory)
    if (allFeatures.length > 0) {
      roadGeoJSON = {
        type:     'FeatureCollection',
        features: allFeatures
      };
    }

    // Optional: Cleanup old layers if memory is an issue (limit to ~3000 segments)
    const layerIds = Object.keys(roadLayers);
    if (layerIds.length > 3000) {
        layerIds.slice(0, 1000).forEach(id => {
            map.removeLayer(roadLayers[id]);
            delete roadLayers[id];
        });
    }

  } catch (err) {
    // Network/API error — log silently, don't crash the map
    console.warn('[SISMAP] Load segments warning:', err.message);
    // Only show toast for real errors, not benign timeouts
    if (!err.message.includes('timeout') && !err.message.includes('abort')) {
      showToast('⚠ Segmen peta gagal dimuat. Akan dicoba ulang...', 'warning', 3000);
    }
  }
}

// ─── UPDATE MARKER GPS ────────────────────────────────────────────
function updateMarker(u) {
  if (!u.lat || !u.lng) return;
  if (markers[u.id]) {
    markers[u.id].setLatLng([u.lat, u.lng]);
  } else {
    markers[u.id] = L.marker([u.lat, u.lng], {
      icon: L.divIcon({ className: 'gps-icon', iconSize: [12, 12] })
    }).addTo(map).bindPopup('<b>Petugas:</b> ' + u.name);
  }
}

// ─── REPORT GPS ───────────────────────────────────────────────────
function reportGPS() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(pos => {
    let lat = pos.coords.latitude;
    let lng = pos.coords.longitude;
    if (roadGeoJSON) {
      try {
        const pt      = turf.point([lng, lat]);
        const snapped = turf.nearestPointOnLine(roadGeoJSON, pt);
        lng = snapped.geometry.coordinates[0];
        lat = snapped.geometry.coordinates[1];
      } catch (e) { console.warn('Snap failed', e); }
    }
    axios.post('/api/gps', { lat, lng }).catch(e => console.error('GPS error', e));
    if (socket?.connected) {
      socket.emit('gps-update', { id: 'me', name: 'Petugas', lat, lng });
    }
    history.push([lat, lng]);
    if (history.length > 1) {
      L.polyline(history, { color: 'cyan', weight: 2, opacity: 0.5, dashArray: '5,5' }).addTo(map);
    }
  }, err => console.warn(err), { enableHighAccuracy: true });
}

// ─── ZOOM TOUR: NAVIGASI JALAN RUSAK ─────────────────────────────
function tourDamaged() {
  if (damagedRoads.length === 0) {
    showToast('Tidak ada jalan rusak yang terdeteksi di sistem.', 'info');
    return;
  }

  if (damagedTourInterval) {
    clearInterval(damagedTourInterval);
    damagedTourInterval = null;
    showToast('Navigasi dihentikan.', 'info');
    return;
  }

  showToast(`Memulai navigasi: ${damagedRoads.length} titik jalan rusak.`, 'success');
  damagedTourIndex = 0;
  
  const next = () => {
    const road = damagedRoads[damagedTourIndex % damagedRoads.length];
    
    // Zoom ke lokasi jalan rusak
    map.setView([road.lat, road.lng], 18, { animate: true, duration: 1.5 });
    
    // Tunggu map loading segmen, lalu buka popup jika ketemu
    setTimeout(() => {
      const segIds = assetLayerMap[road.id] || [];
      for (const sid of segIds) {
        const layer = roadLayers[sid];
        if (layer) {
          layer.openPopup();
          break;
        }
      }
    }, 1200);

    damagedTourIndex++;
  };

  next();
  damagedTourInterval = setInterval(next, 6000); // Ganti tiap 6 detik

  // Matikan otomatis jika user menggerakkan peta secara manual
  map.once('movestart', () => {
    if (damagedTourInterval) {
      clearInterval(damagedTourInterval);
      damagedTourInterval = null;
      console.log('[SISMAP] Tour stopped by user movement');
    }
  });
}

// ─── ZOOM KE ASSET (dipanggil dari socket road-selected) ─────────
function zoomToAsset(assetId, segId) {
  // Coba via assetLayerMap
  const segIds = assetLayerMap[assetId] || (segId ? [segId] : []);
  for (const sid of segIds) {
    const layer = roadLayers[sid];
    if (layer) {
      map.fitBounds(layer.getBounds(), { padding: [100, 100], maxZoom: 17 });
      layer.openPopup();
      return true;
    }
  }
  // Fallback: reload segments di area jalan itu
  return false;
}

// ─── GLOBAL UPDATE CONDITION (dipanggil dari popup button) ────────
window.__sismapUpdateCondition = async function(assetId, segId, newCondition) {
  const statusEl = document.getElementById(`popup-status-${segId}`);
  if (statusEl) statusEl.textContent = '⏳ Menyimpan...';

  try {
    // 1. Update ke database via API
    const res = await fetch(`/api/road-assets/${assetId}/condition`, {
      method:  'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify({ condition: newCondition }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    // 2. Update layer warna langsung di map (tanpa tunggu reload)
    // Segment yang langsung diklik
    updateLayerColor(segId, newCondition);

    // Segment lain yang terkait dengan asset yang sama
    const relatedSegs = assetLayerMap[assetId] || [];
    relatedSegs.forEach(sid => updateLayerColor(sid, newCondition));

    // 3. Broadcast via socket ke semua client (termasuk admin tab lain)
    if (socket?.connected) {
      socket.emit('condition-updated', {
        id:        segId,
        asset_id:  assetId,
        condition: newCondition,
        name:      roadLayers[segId]?._roadData?.name,
      });
    }

    if (statusEl) {
      statusEl.style.color = '#10b981';
      statusEl.textContent = `✓ Kondisi diperbarui: ${labelKondisi(newCondition)}`;
    }

    // Notifikasi visual di tombol yang dipilih
    setTimeout(() => {
      if (statusEl) statusEl.textContent = '';
    }, 3000);

  } catch (err) {
    console.error('Update condition error:', err);
    if (statusEl) {
      statusEl.style.color = '#e11d48';
      statusEl.textContent = '✗ Gagal menyimpan';
    }
  }
};

// ─── MOUNTED ─────────────────────────────────────────────────────
onMounted(() => {
  // Init socket
  socket = io('http://' + window.location.hostname + ':3000');

  // ── Socket events ──
  socket.on('connect', () => {
    console.log('[SISMAP] Socket connected');
  });

  socket.on('gps-update', (data) => {
    updateMarker(data);
  });

  // Admin menyimpan perubahan → map auto-refresh & zoom ke jalan rusak
  socket.on('data-refresh', (payload) => {
    console.log('[SISMAP] data-refresh received:', payload);

    const assetId   = payload.asset_id || payload.id;
    const segId     = payload.id;
    const condition = payload.condition;

    // Update layer warna langsung jika segment sudah di-load
    if (condition) {
      // Coba update via segId
      if (roadLayers[segId]) {
        updateLayerColor(segId, condition);
      }
      // Update semua segment dari asset yang sama
      const relatedSegs = assetLayerMap[assetId] || [];
      relatedSegs.forEach(sid => updateLayerColor(sid, condition));
    }

    // Reload segments untuk memastikan data terbaru
    loadSegments().then(() => {
      // Jika kondisi rusak → zoom ke jalan itu
      if (isDamaged(condition)) {
        const found = zoomToAsset(assetId, segId);
        if (!found && payload.lat && payload.lng) {
          // Fallback: zoom ke koordinat lat/lng dari payload
          map.setView([payload.lat, payload.lng], 16);
        }
      }
    });
  });

  // Map diklik jalan → admin di tab lain auto-scroll ke record itu
  socket.on('road-selected', (data) => {
    console.log('[SISMAP] Road selected (from another tab):', data);
    // Jika dashboard menerima ini (bukan pengirim), zoom ke jalan itu
    if (data.asset_id || data.id) {
      zoomToAsset(data.asset_id, data.id);
    }
  });

  // Sync registry dari server (initial state)
  socket.on('registry-sync', (registry) => {
    console.log('[SISMAP] Registry sync:', Object.keys(registry).length, 'roads');
    // Apply warna dari registry ke layer yang sudah ada
    Object.entries(registry).forEach(([segId, info]) => {
      if (info.condition && roadLayers[segId]) {
        updateLayerColor(segId, info.condition);
      }
    });
  });

  // ── Init map ──
  map = L.map('map', {
    zoomControl:   false,
    preferCanvas:  true
  }).setView([0.7893, 127.3620], 13);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OSM | SISMAP'
  }).addTo(map);

  L.control.zoom({ position: 'bottomleft' }).addTo(map);

  // Legend
  const legend = L.control({ position: 'bottomright' });
  legend.onAdd = () => {
    const div = L.DomUtil.create('div');
    div.style.cssText = 'background:#0f172a;padding:12px;border-radius:12px;border:1px solid #1e293b;font-family:Inter;font-size:11px;color:#94a3b8';
    div.innerHTML = `
      <div style="font-weight:900;font-size:10px;text-transform:uppercase;color:#475569;margin-bottom:8px">LEGENDA</div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><span class="gps-icon" style="position:static;display:inline-block;animation:none;box-shadow:none"></span> Petugas (Live)</div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px"><span style="width:20px;height:4px;background:#10b981;display:inline-block;border-radius:2px"></span> Baik</div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px"><span style="width:20px;height:4px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Sedang</div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px"><span style="width:20px;height:4px;background:#f97316;display:inline-block;border-radius:2px"></span> Rusak Ringan</div>
      <div style="display:flex;align-items:center;gap:8px"><span style="width:20px;height:4px;background:#e11d48;display:inline-block;border-radius:2px"></span> Rusak Berat</div>
    `;
    return div;
  };
  legend.addTo(map);

  // Tombol navigasi jalan rusak
  const tourControl = L.control({ position: 'topright' });
  tourControl.onAdd = function () {
    const div = L.DomUtil.create('div', 'tour-control');
    div.innerHTML = `
      <button onclick="window.tourDamaged()" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-full shadow-lg font-bold text-xs flex items-center gap-2 transition-all">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
        </span>
        NAVIGASI JALAN RUSAK
      </button>
    `;
    return div;
  };
  tourControl.addTo(map);

  window.tourDamaged = tourDamaged;

  // ── Load data awal ──
  loadData();
  loadSegments();

  // Reload segments saat map berpindah/zoom (debounced 400ms)
  let moveTimer;
  map.on('moveend zoomend', () => {
    clearTimeout(moveTimer);
    moveTimer = setTimeout(loadSegments, 400);
  });

  // Polling fallback tiap 60 detik
  setInterval(loadData, 60000);
  setInterval(reportGPS, 5000);
});

onUnmounted(() => {
  if (socket) socket.disconnect();
  if (damagedTourInterval) clearInterval(damagedTourInterval);
});
</script>
