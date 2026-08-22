import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Publieke read-only route-kaart: genummerde divIcon-markers + polylijn over de
// waypoints. Geen default-marker-PNG's (divIcons), dus geen Vite-marker-fix nodig.
// Leunt op admin/route-waypoints.js (renderMap) + het DOM-guard-patroon van
// leaflet-location.js. Data komt uit data-waypoints op de container.

export default function initRouteMap() {
    const el = document.querySelector('[data-route-map]');
    if (!el) return;

    let waypoints = [];
    try {
        waypoints = JSON.parse(el.dataset.waypoints || '[]');
    } catch (e) {
        return;
    }

    const points = waypoints
        .map((wp) => {
            const lat = Number(wp.lat);
            const lng = Number(wp.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
            return { lat, lng, name: wp.name ?? '', notes: wp.notes ?? '' };
        })
        .filter(Boolean);

    if (points.length === 0) return;

    const map = L.map(el, { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18,
    }).addTo(map);

    points.forEach((pt, idx) => {
        const icon = L.divIcon({
            className: 'route-marker',
            html: `<span class="route-marker__num">${idx + 1}</span>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
        L.marker([pt.lat, pt.lng], { icon })
            .addTo(map)
            .bindTooltip(pt.name, { direction: 'top' });
    });

    if (points.length >= 2) {
        // Perzik-accent (--color-accent-1) — cohesief met de genummerde markers.
        L.polyline(
            points.map((p) => [p.lat, p.lng]),
            { color: '#E8A87C', weight: 4, opacity: 0.85 }
        ).addTo(map);
    }

    if (points.length === 1) {
        map.setView([points[0].lat, points[0].lng], 12);
    } else {
        const bounds = L.latLngBounds(points.map((p) => [p.lat, p.lng]));
        map.fitBounds(bounds, { padding: [40, 40] });
    }
}