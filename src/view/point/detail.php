<?php
// Page de détail d'un point avec chargement PHP des données Copernicus.
// Variables attendues depuis le contrôleur : 
// $id_point, $baseURL, $latitude, $longitude, $mesuresRecentes (array),
// $moyennesAnnuelles (array), $donneesAnnuelles (array pour graphique),
// $nombreAnneesSelectionnees, $moyennesSaisonnieres (array)
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

	<!-- Sélecteur de période -->
	<div style="margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
		<form method="GET" action="<?= $baseURL ?>" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
			<input type="hidden" name="action" value="detail">
			<input type="hidden" name="controller" value="point">
			<input type="hidden" name="id" value="<?= intval($id_point ?? 0) ?>">
			
			<label for="years" style="font-weight: bold;">Période d'analyse:</label>
			<select name="years" id="years" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="1" <?= ($nombreAnneesSelectionnees ?? 1) == 1 ? 'selected' : '' ?>>1 an</option>
				<option value="2" <?= ($nombreAnneesSelectionnees ?? 1) == 2 ? 'selected' : '' ?>>2 ans</option>
				<option value="3" <?= ($nombreAnneesSelectionnees ?? 1) == 3 ? 'selected' : '' ?>>3 ans</option>
				<option value="5" <?= ($nombreAnneesSelectionnees ?? 1) == 5 ? 'selected' : '' ?>>5 ans</option>
				<option value="10" <?= ($nombreAnneesSelectionnees ?? 1) == 10 ? 'selected' : '' ?>>10 ans</option>
			</select>
			
			<button type="submit" style="padding: 8px 16px; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
				📊 Actualiser
			</button>
		</form>
	</div>

	<?php if (isset($messageErreur)): ?>
		<div style="margin: 20px 0;">
			<p style="color: #d32f2f; padding: 10px; background-color: #ffebee; border-left: 4px solid #d32f2f;">
				<?= htmlspecialchars($messageErreur) ?>
			</p>
		</div>
	<?php else: ?>
		
		<?php 
		// Noms français pour les variables
		$nomsFrancaisVariables = [
			'so' => 'Salinité',
			'thetao' => 'Température'
		];
		$unitesVariables = [
			'so' => 'PSU',
			'thetao' => '°C'
		];
		?>

		<!-- Valeurs les plus récentes -->
		<h2>Valeurs les plus récentes</h2>
		<div id="valeurs-recentes">
			<?php if (empty($mesuresRecentes)): ?>
				<p>Aucune mesure récente disponible pour ce point.</p>
			<?php else: ?>
				<table style="border-collapse: collapse; width: 100%; max-width: 600px;">
					<thead>
						<tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
							<th style="padding: 10px; text-align: left;">Variable</th>
							<th style="padding: 10px; text-align: left;">Date</th>
							<th style="padding: 10px; text-align: right;">Valeur</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($mesuresRecentes as $cleVariable => $donneesMesure): ?>
							<tr style="border-bottom: 1px solid #eee;">
								<td style="padding: 10px;">
									<strong><?= htmlspecialchars($nomsFrancaisVariables[$cleVariable] ?? $cleVariable) ?></strong>
								</td>
								<td style="padding: 10px;">
									<?= htmlspecialchars($donneesMesure['date']) ?>
								</td>
								<td style="padding: 10px; text-align: right; font-family: monospace;">
									<?= number_format($donneesMesure['valeur'], 4, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<!-- Moyennes sur la période -->
		<h2 style="margin-top: 40px;">
			Moyennes sur <?= intval($nombreAnneesSelectionnees ?? 1) ?> <?= ($nombreAnneesSelectionnees ?? 1) > 1 ? 'ans' : 'an' ?>
			<small style="color: #666; font-size: 14px; font-weight: normal;">
				(<?= htmlspecialchars($dateDebutPeriode ?? '') ?> → <?= htmlspecialchars($dateFinPeriode ?? '') ?>)
			</small>
		</h2>
		<div id="moyennes">
			<?php if (empty($moyennesAnnuelles)): ?>
				<p>Aucune donnée disponible pour calculer les moyennes sur cette période.</p>
			<?php else: ?>
				<table style="border-collapse: collapse; width: 100%; max-width: 800px;">
					<thead>
						<tr style="background-color: #e3f2fd; border-bottom: 2px solid #90caf9;">
							<th style="padding: 10px; text-align: left;">Variable</th>
							<th style="padding: 10px; text-align: right;">Moyenne</th>
							<th style="padding: 10px; text-align: right;">Écart-type (σ)</th>
							<th style="padding: 10px; text-align: right;">Min</th>
							<th style="padding: 10px; text-align: right;">Max</th>
							<th style="padding: 10px; text-align: right;">Mesures</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($moyennesAnnuelles as $cleVariable => $statistiques): ?>
							<tr style="border-bottom: 1px solid #eee;">
								<td style="padding: 10px;">
									<strong><?= htmlspecialchars($nomsFrancaisVariables[$cleVariable] ?? $cleVariable) ?></strong>
								</td>
								<td style="padding: 10px; text-align: right; font-family: monospace;">
									<?= number_format($statistiques['moyenne'], 4, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?>
								</td>
								<td style="padding: 10px; text-align: right; font-family: monospace; color: #1976d2;">
									±<?= number_format($statistiques['ecartType'], 4, ',', ' ') ?>
								</td>
								<td style="padding: 10px; text-align: right; font-family: monospace;">
									<?= number_format($statistiques['minimum'], 4, ',', ' ') ?>
								</td>
								<td style="padding: 10px; text-align: right; font-family: monospace;">
									<?= number_format($statistiques['maximum'], 4, ',', ' ') ?>
								</td>
								<td style="padding: 10px; text-align: right; color: #666;">
									<?= number_format($statistiques['nombreMesures'], 0, ',', ' ') ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div style="margin-top: 10px;">
					<small style="color: #666;">
						💡 L'écart-type (σ) mesure la variabilité : plus il est élevé, plus les valeurs fluctuent.
					</small>
				</div>
			<?php endif; ?>
		</div>

		<!-- Moyennes saisonnières -->
		<h2 style="margin-top: 40px;">Moyennes saisonnières</h2>
		<div id="moyennes-saisonnieres">
			<?php if (!isset($moyennesSaisonnieres) || empty($moyennesSaisonnieres)): ?>
				<p>Aucune donnée disponible pour calculer les moyennes saisonnières.</p>
			<?php else: ?>
				<?php 
				// Ordre des saisons
				$ordreSaisons = ['Hiver', 'Printemps', 'Été', 'Automne'];
				$couleursSaisons = [
					'Hiver' => '#64b5f6',
					'Printemps' => '#81c784',
					'Été' => '#ffb74d',
					'Automne' => '#ba68c8'
				];
				?>
				
				<?php foreach ($moyennesSaisonnieres as $cleVariable => $donneesSaison): ?>
					<h3 style="margin-top: 30px; color: #424242;">
						<?= htmlspecialchars($nomsFrancaisVariables[$cleVariable] ?? $cleVariable) ?>
					</h3>
					<table style="border-collapse: collapse; width: 100%; max-width: 700px; margin-bottom: 20px;">
						<thead>
							<tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
								<th style="padding: 10px; text-align: left;">Saison</th>
								<th style="padding: 10px; text-align: right;">Moyenne</th>
								<th style="padding: 10px; text-align: right;">Min</th>
								<th style="padding: 10px; text-align: right;">Max</th>
								<th style="padding: 10px; text-align: right;">Mesures</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($ordreSaisons as $saison): ?>
								<?php if (isset($donneesSaison[$saison])): ?>
									<?php $statistiques = $donneesSaison[$saison]; ?>
									<tr style="border-bottom: 1px solid #eee;">
										<td style="padding: 10px; border-left: 4px solid <?= $couleursSaisons[$saison] ?>;">
											<strong><?= htmlspecialchars($saison) ?></strong>
										</td>
										<td style="padding: 10px; text-align: right; font-family: monospace;">
											<?= number_format($statistiques['moyenne'], 4, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?>
										</td>
										<td style="padding: 10px; text-align: right; font-family: monospace;">
											<?= number_format($statistiques['minimum'], 4, ',', ' ') ?>
										</td>
										<td style="padding: 10px; text-align: right; font-family: monospace;">
											<?= number_format($statistiques['maximum'], 4, ',', ' ') ?>
										</td>
										<td style="padding: 10px; text-align: right; color: #666;">
											<?= number_format($statistiques['nombreMesures'], 0, ',', ' ') ?>
										</td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endforeach; ?>
				
				<div style="margin-top: 15px;">
					<small style="color: #666;">
						🌍 Saisons basées sur l'hémisphère nord : 
						<span style="color: <?= $couleursSaisons['Hiver'] ?>">■</span> Hiver (Déc-Fév) | 
						<span style="color: <?= $couleursSaisons['Printemps'] ?>">■</span> Printemps (Mar-Mai) | 
						<span style="color: <?= $couleursSaisons['Été'] ?>">■</span> Été (Juin-Août) | 
						<span style="color: <?= $couleursSaisons['Automne'] ?>">■</span> Automne (Sep-Nov)
					</small>
				</div>
			<?php endif; ?>
		</div>

		<!-- Graphique d'évolution -->
		<?php if (!empty($donneesAnnuelles)): ?>
			<h2 style="margin-top: 40px;">
				Évolution sur <?= intval($nombreAnneesSelectionnees ?? 1) ?> <?= ($nombreAnneesSelectionnees ?? 1) > 1 ? 'ans' : 'an' ?>
			</h2>
			<div style="margin-top: 20px; max-width: 900px;">
				<canvas id="graphique-evolution" style="max-height: 400px;"></canvas>
			</div>

			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
			<script>
			// Données PHP converties en JavaScript
			const donneesAnnuelles = <?= json_encode($donneesAnnuelles) ?>;
			const nomsFrancaisVariables = <?= json_encode($nomsFrancaisVariables) ?>;
			const unitesVariables = <?= json_encode($unitesVariables) ?>;

			// Préparer les datasets pour Chart.js
			const ensemblesDonnees = [];
			const couleurs = {
				'so': {bordure: 'rgb(54, 162, 235)', fond: 'rgba(54, 162, 235, 0.1)'},
				'thetao': {bordure: 'rgb(255, 99, 132)', fond: 'rgba(255, 99, 132, 0.1)'}
			};

			// Extraire les labels (dates uniques)
			const etiquettes = [...new Set(donneesAnnuelles.map(d => d.date))].sort();

			// Créer un dataset par variable
			const variables = Object.keys(donneesAnnuelles[0]?.valeurs || {});
			
			variables.forEach(cleVariable => {
				const donnees = etiquettes.map(date => {
					const entree = donneesAnnuelles.find(d => d.date === date);
					return entree?.valeurs[cleVariable] ?? null;
				});

				ensemblesDonnees.push({
					label: nomsFrancaisVariables[cleVariable] || cleVariable,
					data: donnees,
					borderColor: couleurs[cleVariable]?.bordure || 'rgb(75, 192, 192)',
					backgroundColor: couleurs[cleVariable]?.fond || 'rgba(75, 192, 192, 0.1)',
					borderWidth: 2,
					tension: 0.1,
					yAxisID: cleVariable // Un axe Y par variable
				});
			});

			// Créer le graphique
			const contexte = document.getElementById('graphique-evolution').getContext('2d');
			const graphique = new Chart(contexte, {
				type: 'line',
				data: {
					labels: etiquettes,
					datasets: ensemblesDonnees
				},
				options: {
					responsive: true,
					maintainAspectRatio: true,
					interaction: {
						mode: 'index',
						intersect: false,
					},
					plugins: {
						title: {
							display: true,
							text: 'Évolution des mesures océanographiques'
						},
						legend: {
							display: true,
							position: 'top'
						},
						tooltip: {
							callbacks: {
								label: function(context) {
									const cleVariable = context.dataset.yAxisID;
									const unite = unitesVariables[cleVariable] || '';
									return context.dataset.label + ': ' + context.parsed.y.toFixed(4) + ' ' + unite;
								}
							}
						}
					},
					scales: {
						x: {
							display: true,
							title: {
								display: true,
								text: 'Date'
							},
							ticks: {
								maxTicksLimit: 20,
								maxRotation: 45,
								minRotation: 45
							}
						},
						// Axe Y pour la salinité
						so: {
							type: 'linear',
							display: variables.includes('so'),
							position: 'left',
							title: {
								display: true,
								text: 'Salinité (PSU)',
								color: couleurs.so?.bordure
							},
							ticks: {
								color: couleurs.so?.bordure
							}
						},
						// Axe Y pour la température
						thetao: {
							type: 'linear',
							display: variables.includes('thetao'),
							position: 'right',
							title: {
								display: true,
								text: 'Température (°C)',
								color: couleurs.thetao?.bordure
							},
							ticks: {
								color: couleurs.thetao?.bordure
							},
							grid: {
								drawOnChartArea: false
							}
						}
					}
				}
			});
			</script>
		<?php endif; ?>

		<div style="margin-top: 30px;">
			<small style="color: #666;">
				Données récupérées depuis Copernicus Marine Service
			</small>
		</div>

	<?php endif; ?>
</div>