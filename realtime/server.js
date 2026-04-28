const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
app.use(express.json());
const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: '*' }
});

// Simple in-memory registry: { roadId: { condition, name, lat, lng, updatedAt } }
const roadRegistry = {};

io.on('connection', (socket) => {
    console.log('[SISMAP] User connected:', socket.id);

    // Kirim state terakhir ke client baru agar langsung sync
    if (Object.keys(roadRegistry).length > 0) {
        socket.emit('registry-sync', roadRegistry);
    }

    // Handle GPS update dari petugas lapangan
    socket.on('gps-update', (data) => {
        socket.broadcast.emit('gps-update', data);
    });

    // Handle klik garis jalan di Map → beritahu semua admin panel
    // Payload: { id, asset_id, name, condition, lat, lng }
    socket.on('road-selected', (data) => {
        console.log('[SISMAP] Road selected from map:', data);
        // Broadcast ke semua tab admin yang terbuka
        io.emit('road-selected', data);
    });

    // Handle update kondisi dari map popup
    // Payload: { id, asset_id, condition, name }
    socket.on('condition-updated', (data) => {
        console.log('[SISMAP] Condition updated from map:', data);
        if (data.id) {
            roadRegistry[data.id] = {
                ...roadRegistry[data.id],
                ...data,
                updatedAt: new Date().toISOString()
            };
        }
        // Broadcast ke semua client (dashboard + admin)
        io.emit('data-refresh', {
            ...data,
            message: 'Kondisi jalan diperbarui dari peta'
        });
    });

    socket.on('disconnect', () => {
        console.log('[SISMAP] User disconnected:', socket.id);
    });
});

// Endpoint untuk Laravel push updates (road condition changes dari admin)
app.post('/broadcast', (req, res) => {
    const { event, data } = req.body;
    
    if (!event || !data) {
        return res.status(400).json({ status: 'error', message: 'Invalid payload structure' });
    }

    const id = data.asset_id || data.id;

    if (event === 'data-refresh') {
        if (!id) {
            console.warn('[SISMAP] [WARNING] Broadcast data-refresh received without ID!', data);
        } else {
            roadRegistry[id] = {
                ...roadRegistry[id],
                ...data,
                updatedAt: new Date().toISOString()
            };
            console.log(`[SISMAP] Broadcast '${event}' → Asset #${id} (${data.name || 'Unknown'}) kondisi: ${data.condition || 'N/A'}`);
        }
    }

    io.emit(event, data);
    return res.json({ status: 'ok', clients: io.engine.clientsCount });
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'running',
        clients: io.engine.clientsCount,
        registrySize: Object.keys(roadRegistry).length
    });
});

const PORT = 3000;
server.listen(PORT, '0.0.0.0', () => {
    console.log(`[SISMAP] Realtime server running on port ${PORT}`);
});
