<?php
// Page de détail d'un point avec chargement PHP des données Copernicus.
// Variables attendues depuis le contrôleur : 
// $id_point, $baseURL, $latitude, $longitude, $measurements (array des mesures)
?>
<div>
	<h2>Détail du point #<?= htmlspecialchars($id_point ?? 0) ?></h2>
	<p><a href="<?= $baseURL ?>?action=carte&controller=point">Retour à la carte</a></p>

	<div>
		<label>ID du point: </label><span id="point-id"><?= intval($id_point ?? 0) ?></span>
	</div>

	<div>
		<label>Coordonnées: </label>
		<span>Latitude: <?= number_format($latitude ?? 0, 6) ?>° / Longitude: <?= number_format($longitude ?? 0, 6) ?>°</span>
	</div>

	<h2>Valeurs les plus récentes</h2>
	<div id="values">
		<?php if (isset($error_message)): ?>
			<p style="color: #d32f2f;"><?= htmlspecialchars($error_message) ?></p>
		<?php elseif (empty($measurements)): ?>
			<p>Aucune mesure disponible pour ce point.</p>
		<?php else: ?>
			<?php 
			// Noms français pour les variables
			$varNames = [
				'so' => 'Salinité',
				'thetao' => 'Température'
			];
			?>
			<table style="border-collapse: collapse; width: 100%; max-width: 600px;">
				<thead>
					<tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
						<th style="padding: 10px; text-align: left;">Variable</th>
						<th style="padding: 10px; text-align: left;">Date</th>
						<th style="padding: 10px; text-align: right;">Valeur</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($measurements as $varKey => $data): ?>
						<tr style="border-bottom: 1px solid #eee;">
							<td style="padding: 10px;">
								<strong><?= htmlspecialchars($varNames[$varKey] ?? $varKey) ?></strong>
							</td>
							<td style="padding: 10px;">
								<?= htmlspecialchars($data['date']) ?>
							</td>
							<td style="padding: 10px; text-align: right; font-family: monospace;">
								<?= number_format($data['value'], 4, ',', ' ') ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<?php if (!empty($measurements)): ?>
		<div style="margin-top: 20px;">
			<small style="color: #666;">
				Données récupérées depuis Copernicus Marine Service
			</small>
		</div>
	<?php endif; ?>
</div>