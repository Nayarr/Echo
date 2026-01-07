<?php
// Page minimale de détail d'un point.
// Variables attendues depuis le contrôleur : $id_point, $baseURL
?>
<div>
	<h2>Détail du point #<?= htmlspecialchars($id_point ?? 0) ?></h2>
	<p><a href="<?= $baseURL ?>?action=carte&controller=point">Retour à la carte</a></p>

	<div>
		<label>ID du point: </label><span id="point-id"><?= intval($id_point ?? 0) ?></span>
	</div>

	<div><!-- Période automatique (aucun champ requis) --></div>

	<h2>Valeurs</h2>
	<div id="values">—</div>

</div>

<script>
const baseURL = '<?= $baseURL ?>';
const pointId = <?= intval($id_point ?? 0) ?>;
const pointLat = <?= isset($latitude) ? floatval($latitude) : 0 ?>;
const pointLon = <?= isset($longitude) ? floatval($longitude) : 0 ?>;

// Noms français pour les variables
const varNames = {
	'so': 'Salinité',
	'thetao': 'Température'
};

async function loadMeasurements() {
	// Récupère les dates entrées ; si aucune plage fournie on utilise 365 jours par défaut
	const startElem = document.getElementById('start');
	const endElem = document.getElementById('end');
	let start = startElem ? startElem.value : '';
	let end = endElem ? endElem.value : '';
	if (!start || !end) {
		const d = new Date();
		end = d.toISOString().slice(0,10);
		const s = new Date(d);
		s.setDate(d.getDate() - 365);
		start = s.toISOString().slice(0,10);
	}

	const url = `${baseURL}?action=apiCopernicusPoint&controller=point&lat=${encodeURIComponent(pointLat)}&lon=${encodeURIComponent(pointLon)}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&dataset=cmems_mod_glo_phy_anfc_0.083deg_PT1H-m&variables=so,thetao`;

	document.getElementById('values').textContent = 'Chargement...';

	try {
		const res = await fetch(url);
		const json = await res.json();


		if (!Array.isArray(json)) {
			document.getElementById('values').textContent = json.error ? ('Erreur API: ' + json.error) : JSON.stringify(json);
			return;
		}

		// Trier par date décroissante pour prendre les plus récentes
		json.sort((a,b) => new Date(b.date) - new Date(a.date));

		// Conserver la dernière mesure non-nulle pour chaque variable
		const latest = {};
		for (const row of json) {
			const vals = row.values || {};
			for (const [k, v] of Object.entries(vals)) {
				if ((v !== null) && (latest[k] === undefined)) {
					latest[k] = {date: row.date, value: v};
				}
			}
		}

		// Affichage simple : une ligne par variable (nom français)
		const lines = [];
		for (const [k, o] of Object.entries(latest)) {
			const name = varNames[k] || k;
			lines.push(`${name} : ${o.date} , ${o.value}`);
		}
		if (lines.length === 0) {
			document.getElementById('values').textContent = 'Aucune mesure trouvée sur la période.';
		} else {
			document.getElementById('values').textContent = lines.join('\n');
		}

	} catch (e) {
		document.getElementById('values').textContent = 'Erreur: ' + e;
	}
}

// Charger automatiquement la dernière mesure pour chaque variable à l'ouverture
loadMeasurements();
</script>

