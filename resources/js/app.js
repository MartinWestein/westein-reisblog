import 'bootstrap';
import Alpine from 'alpinejs';
import initLocationMap from './leaflet-location';
import initRouteMap from './leaflet-route';

window.Alpine = Alpine;
Alpine.start();

initLocationMap(); // guard binnenin regelt no-op op niet-kaart-pagina's
initRouteMap();    // idem
