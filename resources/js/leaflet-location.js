import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Vite-marker-landmine: default-marker-PNG's resolven niet zonder deze fix
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

export default function initLocationMap() {
    const el = document.querySelector('[data-location-map]');
    if (!el) return;

    const lat = Number(el.dataset.lat);
    const lng = Number(el.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    const map = L.map(el, { scrollWheelZoom: false }).setView([lat, lng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    const name = el.dataset.name ?? '';
    L.marker([lat, lng])
        .addTo(map)
        .bindTooltip(name, { permanent: true, direction: 'top' });
}