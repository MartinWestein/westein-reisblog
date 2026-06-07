import 'bootstrap';
import { Tooltip } from 'bootstrap';
import Alpine from 'alpinejs';

import { galleryUpload } from './admin/gallery-upload.js';
import { imageUpload } from './admin/image-upload.js';
import { tiptapSimple } from './admin/tiptap-simple.js';

// Alpine data factories registreren VOORDAT Alpine.start() draait
document.addEventListener('alpine:init', () => {
    Alpine.data('imageUpload', imageUpload);
    Alpine.data('tiptapSimple', tiptapSimple);
    Alpine.data('galleryUpload', galleryUpload);
});

window.Alpine = Alpine;
Alpine.start();

// Bootstrap tooltips overal activeren
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new Tooltip(el);
    });
});
