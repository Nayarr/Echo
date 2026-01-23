<div class="nav-func">
  <!-- Navigate by ID -->
  <div class="nav-box nav-box-id">
    <form method="GET" action="<?= $baseURL ?>">
      <input type="hidden" name="controller" value="point">
      <input type="hidden" name="action" value="detail">
      <input type="number" name="id" placeholder="ID du point" required>
      <button type="submit" class="btn-submit">Voir le point</button>
    </form>
  </div>

  <!-- Navigate by coordinates -->
  <div class="nav-box nav-box-coords">
    <form method="GET" action="<?= $baseURL ?>">
      <input type="hidden" name="controller" value="point">
      <input type="hidden" name="action" value="rechercheParCoordonnees">
      <div class="input-group">
        <input type="number" name="lat" placeholder="Latitude" step="any" required>
        <input type="number" name="lon" placeholder="Longitude" step="any" required>
      </div>
      <button type="submit" class="btn-submit">Voir le point</button>
    </form>
  </div>
</div>

<div id="map"></div>

<script src="https://unpkg.com/maplibre-gl@3.6.0/dist/maplibre-gl.js"></script>

<script>
// baseURL fourni par PHP pour construire les liens vers frontController
const baseURL = '<?= $baseURL ?>';
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
        <br><br>
        <a href="${baseURL}?action=detail&controller=point&id=${data.id_point}">Voir le détail</a>
      `)
      .addTo(map);
  } catch (err) {
    console.error("Erreur nearest point :", err);
  }
});
</script>
