<style>
main.carte-view {
  margin: 0;
  padding: 0;
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
}

#map {
  width: 100%;
  height: 100%;
  flex: 1;
  border: 2px solid rgba(0,255,0,0.6);
}

/* Popup styling: anthracite background with white text */
.maplibregl-popup-content {
  background: #2b2b2b !important;
  color: #ffffff !important;
  border-radius: 6px !important;
  padding: 8px 10px !important;
  box-shadow: 0 6px 18px rgba(0,0,0,0.45) !important;
}

.maplibregl-popup-content strong {
  color: #ffffff !important;
}

/* Popup tip (the little triangle) should match background */
.maplibregl-popup-tip {
  border-top-color: #2b2b2b !important;
}

/* Coordinates box (bottom-left) */
.coords-box {
  position: absolute;
  left: 10px;
  bottom: 10px;
  background: rgba(43,43,43,0.9);
  color: #fff;
  padding: 6px 10px;
  border-radius: 4px;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  z-index: 9999;
  pointer-events: none;
  white-space: nowrap;
}

</style>

<div id="map"></div>

<script src="https://unpkg.com/maplibre-gl@3.6.0/dist/maplibre-gl.js"></script>

<script>
const map = new maplibregl.Map({
  container: 'map',
  style: 'https://demotiles.maplibre.org/style.json',
  center: [-150, 0],
  zoom: 3,
  renderWorldCopies: true
});

map.addControl(new maplibregl.NavigationControl());
// Disable panning, keep zoom controls active
map.dragPan.disable();
if (map.dragRotate) map.dragRotate.disable();
map.boxZoom.disable();
map.keyboard.disable();
map.touchZoomRotate.enable();
map.scrollZoom.enable();
map.doubleClickZoom.enable();

// Add coordinates display box
const coordsBox = document.createElement('div');
coordsBox.className = 'coords-box';
coordsBox.textContent = 'Lat: --, Lon: --';
document.getElementById('map').appendChild(coordsBox);

// Update coords on mouse move
map.on('mousemove', (e) => {
  coordsBox.textContent = `Lat: ${e.lngLat.lat.toFixed(6)}, Lon: ${e.lngLat.lng.toFixed(6)}`;
});

// Hide coords when mouse leaves map
map.on('mouseleave', () => {
  coordsBox.textContent = '';
});

// ----------------------
// CLICK SUR LA CARTE - FIND NEAREST POINT
// ----------------------
const SEARCH_RADIUS = 8; // km

map.on('click', async (e) => {
  const clickLat = e.lngLat.lat;
  const clickLon = e.lngLat.lng;

  const url =
    `/Echo/web/frontController.php?action=apiNearestPoint&controller=point`
    + `&lat=${clickLat}&lon=${clickLon}`
    + `&radius=${SEARCH_RADIUS}`;

  try {
    const res = await fetch(url);
    const data = await res.json();

    if (!data || !data.id_point) {
      new maplibregl.Popup()
        .setLngLat([clickLon, clickLat])
        .setHTML(`
          <strong>Aucun point trouvé</strong><br>
          <br>
          Aucun point dans un rayon de ${SEARCH_RADIUS} km<br>
          <br>
          Coordonnées du clic:<br>
          Lat: ${clickLat.toFixed(6)}<br>
          Lon: ${clickLon.toFixed(6)}
        `)
        .addTo(map);
      return;
    }

    new maplibregl.Popup()
      .setLngLat([clickLon, clickLat])
      .setHTML(`
        <strong>Point trouvé</strong><br>
        <br>
        <strong>Clique:</strong><br>
        Lat: ${clickLat.toFixed(6)}<br>
        Lon: ${clickLon.toFixed(6)}<br>
        <br>
        <strong>Point #${data.id_point}:</strong><br>
        Lat: ${data.latitude}<br>
        Lon: ${data.longitude}<br>
        Distance: ${data.distance.toFixed(2)} km
      `)
      .addTo(map);
  } catch (err) {
    console.error("Erreur nearest point :", err);
  }
});
</script>

<div id="point-details" style="display:none; padding: 20px; background: white; border-radius: 20px; position: fixed; bottom: 20px; left: 20px; z-index: 2000; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
    
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 id="detail-title">Nom du point</h3>
        
        <button id="fav-btn" class="btn-fav" onclick="toggleFavori()">
            ♥ </button>
    </div>
    
    <p id="detail-desc">Description...</p>
</div>

<script>
    // Variable globale pour stocker l'ID du point actuel affiché
    let currentPointId = null;

    // Fonction appelée quand on clique sur le coeur
    function toggleFavori() {
        if (!currentPointId) return;

        const btn = document.getElementById('fav-btn');

        // Appel AJAX vers votre contrôleur PHP
        fetch(`frontController.php?controller=utilisateur&action=toggleFavori&id_point=${currentPointId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Si ajouté, on met la classe active (rouge)
                    if (data.action === 'added') {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                } else {
                    alert("Erreur : " + data.message); // Probablement pas connecté
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Fonction fictive pour simuler l'ouverture d'un point sur la carte
    // Vous devez appeler ça quand on clique sur un marqueur de la carte
    function openPointDetails(id, name, isFavorite) {
        currentPointId = id;
        document.getElementById('detail-title').innerText = name;
        document.getElementById('point-details').style.display = 'block';

        // Gérer l'état initial du bouton (si c'est déjà un favori ou pas)
        const btn = document.getElementById('fav-btn');
        if (isFavorite) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    }
</script>
