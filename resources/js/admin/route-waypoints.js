import Sortable from 'sortablejs';
import { Modal } from 'bootstrap';

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

export default function routeWaypoints({
    initialWaypoints = [],
    locations = [],
    initialDestinationId = null,
}) {
    return {
        waypoints: initialWaypoints,
        locations,
        destinationId: initialDestinationId,
        selectedLocationId: '',
        sortable: null,

        init() {
            this.serialize();

            this.$watch('waypoints', () => this.serialize());

            this.$nextTick(() => this.bindSortable());
        },

        bindSortable() {
            const list = this.$el.querySelector('[data-waypoint-list]');
            if (!list) return;

            this.sortable = Sortable.create(list, {
                handle: '.waypoint-row__handle',
                animation: 150,
                onEnd: (event) => {
                    if (event.oldIndex === event.newIndex) return;

                    // SortableJS muteert DOM; we revert dat en laten x-for opnieuw
                    // renderen vanuit het Alpine-model. Voorkomt desync tussen
                    // DOM-volgorde en model-volgorde.
                    event.from.insertBefore(event.item, event.from.children[event.oldIndex]);

                    const moved = this.waypoints.splice(event.oldIndex, 1)[0];
                    this.waypoints.splice(event.newIndex, 0, moved);

                    // Force reactive notify (splice muteert in place, maar
                    // sommige Alpine-versies pikken nested mutations niet altijd op)
                    this.waypoints = [...this.waypoints];
                },
            });
        },

        get locationsById() {
            return Object.fromEntries(this.locations.map(l => [Number(l.id), l]));
        },

        get availableLocations() {
            if (!this.destinationId) return [];
            const did = Number(this.destinationId);
            return this.locations.filter(l => Number(l.destination_id) === did);
        },

        locationName(locationId) {
            const l = this.locationsById[Number(locationId)];
            return l ? l.name : '(onbekende locatie)';
        },

        isWaypointOutsideDestination(index) {
            const wp = this.waypoints[index];
            if (!this.destinationId || !wp) return false;
            const l = this.locationsById[Number(wp.location_id)];
            if (!l) return false;
            return Number(l.destination_id) !== Number(this.destinationId);
        },

        addWaypoint() {
            if (!this.selectedLocationId) return;
            this.waypoints.push({
                _uid: 'new-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
                location_id: Number(this.selectedLocationId),
                notes: '',
            });
            this.waypoints = [...this.waypoints];
            this.selectedLocationId = '';
        },

        removeWaypoint(index) {
            this.waypoints.splice(index, 1);
            this.waypoints = [...this.waypoints];
        },

        updateNotes(index, value) {
            this.waypoints[index].notes = value;
            this.serialize();
        },

        serialize() {
            if (!this.$refs.hidden) return;

            const cleaned = this.waypoints.map(wp => ({
                location_id: wp.location_id,
                notes: wp.notes ?? '',
            }));
            this.$refs.hidden.value = JSON.stringify(cleaned);
        },

        mapInstance: null,

        openMapPreview() {
            const modalEl = document.getElementById('routeMapPreviewModal');
            if (!modalEl) return;

            const modal = Modal.getOrCreateInstance(modalEl);

            // Leaflet rendert verkeerd in hidden containers — wacht tot
            // de modal echt zichtbaar is, dan pas instantiëren.
            modalEl.addEventListener(
                'shown.bs.modal',
                () => this.renderMap(),
                { once: true }
            );
            modalEl.addEventListener(
                'hidden.bs.modal',
                () => this.destroyMap(),
                { once: true }
            );

            modal.show();
        },

        renderMap() {
            const canvas = document.getElementById('routeMapPreviewCanvas');
            if (!canvas) return;

            const points = this.waypoints
                .map(wp => {
                    const loc = this.locationsById[Number(wp.location_id)];
                    if (!loc) return null;
                    const lat = Number(loc.latitude);
                    const lng = Number(loc.longitude);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
                    return { lat, lng, name: loc.name, notes: wp.notes ?? '' };
                })
                .filter(Boolean);

            if (this.mapInstance) this.destroyMap();

            this.mapInstance = window.L.map(canvas, { scrollWheelZoom: false });

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(this.mapInstance);

            if (points.length === 0) {
                this.mapInstance.setView([50, 10], 4); // Europa centraal-ish
                return;
            }

            points.forEach((pt, idx) => {
                const marker = window.L.marker([pt.lat, pt.lng]).addTo(this.mapInstance);
                const label = `<strong>${idx + 1}. ${escapeHtml(pt.name)}</strong>`
                    + (pt.notes ? `<br><span style="color:#5C6779;">${escapeHtml(pt.notes)}</span>` : '');
                marker.bindPopup(label);
            });

            if (points.length >= 2) {
                window.L.polyline(
                    points.map(p => [p.lat, p.lng]),
                    { color: '#1E90FF', weight: 3, opacity: 0.8 }
                ).addTo(this.mapInstance);
            }

            if (points.length === 1) {
                this.mapInstance.setView([points[0].lat, points[0].lng], 10);
            } else {
                const bounds = window.L.latLngBounds(points.map(p => [p.lat, p.lng]));
                this.mapInstance.fitBounds(bounds, { padding: [40, 40] });
            }
        },

        destroyMap() {
            if (this.mapInstance) {
                this.mapInstance.remove();
                this.mapInstance = null;
            }
        },
    };
}
