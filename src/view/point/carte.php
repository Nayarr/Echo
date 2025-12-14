<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
}
#map {
    width: 100%;
    height: 100%;
}
</style>

<script src="https://unpkg.com/deck.gl@8.9.28/dist.min.js"></script>
</head>

<body>
<div id="map"></div>

<script>
const {DeckGL, ScatterplotLayer, TileLayer, BitmapLayer, MapView} = deck;

const API_URL = window.location.origin + "/Echo/web/frontController.php";

// ---- Chargement API ----
async function loadPoints(zoom) {
    const url = `${API_URL}?action=apiPoints&controller=point&zoom=${zoom}`;
    const res = await fetch(url);
    return await res.json();
}

// ---- Deck.gl ----
let currentZoom = 2;

const deckgl = new DeckGL({
    container: 'map',
    views: new MapView({ repeat: true }),
    initialViewState: {
        longitude: 0,
        latitude: 0,
        zoom: currentZoom
    },
    controller: true
});

async function updateLayers(zoom) {
    const points = await loadPoints(zoom);

    deckgl.setProps({
        layers: [
            // 🌍 FOND DE CARTE OSM
            new TileLayer({
                data: 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                minZoom: 0,
                maxZoom: 19,
                tileSize: 256,
                renderSubLayers: props => {
                    const {west, south, east, north} = props.tile.bbox;
                    return new BitmapLayer(props, {
                        image: props.data,
                        bounds: [west, south, east, north]
                    });
                }
            }),

            // 🔴 POINTS
            new ScatterplotLayer({
                id: 'points',
                data: points,
                getPosition: d => [
                    parseFloat(d.longitude),
                    parseFloat(d.latitude)
                ],
                getRadius: zoom < 5 ? 30000 : 8000,
                radiusUnits: 'meters',
                getFillColor: [255, 0, 0, 150],
                pickable: true
            })
        ]
    });
}

// ---- événements ----
deckgl.setProps({
    onViewStateChange: ({viewState}) => {
        const z = Math.floor(viewState.zoom);
        if (z !== currentZoom) {
            currentZoom = z;
            updateLayers(z);
        }
    }
});

// chargement initial
updateLayers(currentZoom);
</script>

</body>
</html>
