import './bootstrap';
import { createApp } from 'vue';
import MapView from './components/MapView.vue';
import PulseGraph from './components/PulseGraph.vue';
import StatsPanel from './components/StatsPanel.vue';

const app = createApp({});

app.component('map-view', MapView);
app.component('pulse-graph', PulseGraph);
app.component('stats-panel', StatsPanel);

app.mount('#app');