import 'bootstrap';
import { Tooltip } from 'bootstrap';
import Alpine from 'alpinejs';

import { galleryUpload } from './admin/gallery-upload.js';
import { imageUpload } from './admin/image-upload.js';
import { tiptapSimple } from './admin/tiptap-simple.js';
import { tiptapRich } from './admin/tiptap-rich.js';
import { tagPills } from './admin/tag-pills.js';
import routeWaypoints from './admin/route-waypoints.js';
import './admin/image-picker.js'
import { registerMediaSelectionStore } from './admin/media-selection.js';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Leaflet's default marker-icons breken in Vite-builds (path-issues).
// Standaard-fix: programmatisch de juiste image-URLs registreren.
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

window.L = L;

// Alpine data factories registreren VOORDAT Alpine.start() draait
document.addEventListener('alpine:init', () => {
    Alpine.data('imageUpload', imageUpload);
    Alpine.data('tiptapSimple', tiptapSimple);
    Alpine.data('tiptapRich', tiptapRich);
    Alpine.data('tagPills', tagPills);
    Alpine.data('galleryUpload', galleryUpload);
    Alpine.data('routeWaypoints', routeWaypoints);
    registerMediaSelectionStore(Alpine);
});

window.Alpine = Alpine;
Alpine.start();

// Bootstrap tooltips overal activeren
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new Tooltip(el);
    });
});
