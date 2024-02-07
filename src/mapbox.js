import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

mapboxgl.accessToken = 'pk.eyJ1IjoibWJha2VyLXJhdGhhbmEiLCJhIjoiY2xsdWk5MTQ4MHp2dTNlcWY1YXAxdXB6YSJ9.O2odz3WBo9hsNIBXHc8kiQ';

export const projectionTypes = {
    globe: 'globe',
    // equal area
    equalEarth: 'equalEarth',
    // equal compromise
    naturalEarth: 'naturalEarth',
    winkelTripel: 'winkelTripel',
    // rectangular
    equirectangular: 'equirectangular',
    mercator: 'mercator',
    default: 'mercator',
    behrmann: {name: 'albers', parallels: [-30, 30]},
    'hobo-dyer': {name: 'albers', parallels: [-37.5, 37.5]},
    'gall-peters': {name: 'albers', parallels: [-45, 45]},
}

export function createMap (container = 'map', opts = {}) {
    return new mapboxgl.Map({
        container, // container ID
        style: 'mapbox://styles/mapbox/streets-v12', // style URL
        center: [-74.5, 40], // starting position [lng, lat]
        zoom: 3, // starting zoom
        renderWorldCopies: false,
        projection: projectionTypes.default,
        ...(opts && opts)
    });
}

export function getBounds () {
    return new mapboxgl.LngLatBounds([0,0,0,0]);
}