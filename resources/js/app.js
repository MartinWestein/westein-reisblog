import 'bootstrap';
import Alpine from 'alpinejs';
import initLocationMap from './leaflet-location';

window.Alpine = Alpine;
Alpine.start();

initLocationMap(); // guard binnenin regelt no-op op niet-kaart-pagina's
