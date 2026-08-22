import 'bootstrap';
import Alpine from 'alpinejs';
import initLocationMap from './leaflet-location';
import initRouteMap from './leaflet-route';
import photoLightbox from './photo-lightbox';

window.Alpine = Alpine;
Alpine.data('photoLightbox', photoLightbox);
Alpine.start();

initLocationMap(); // guard binnenin regelt no-op op niet-kaart-pagina's
initRouteMap();    // idem
