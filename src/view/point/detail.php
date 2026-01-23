<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du point</title>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <div class="card detail-card">
                <div class="detail">
                    <h1>Détail du point</h1> 
                    <span class="id">ID <?= htmlspecialchars($idPoint ?? '1062420') ?></span>
                </div>
                <p><span>Latitude</span> <?= htmlspecialchars($latitude ?? '-0.916664') ?>°</p>
                <p><span>Longitude</span> <?= htmlspecialchars($longitude ?? '211.416687') ?>°</p>
                <p><span>Dernière mesure :</span> <?= htmlspecialchars($derniereMesure ?? '2026-01-19') ?></p>
            </div>

        <?php $nomsFrancaisVariables = [
                    'so' => 'Salinité',
                    'thetao' => 'Température'
                ];
                $unitesVariables = [
                    'so' => 'PSU',
                    'thetao' => '°C'
        ]; ?>

            <!-- Moyennes saisonnières -->
            <div class="seasons-section">
                <?php if (!isset($moyennesSaisonnieres) || empty($moyennesSaisonnieres)): ?>
                    <p>Aucune donnée disponible pour calculer les moyennes saisonnières.</p>
                <?php else: ?>
                    <?php 
                    // Ordre des saisons
                    $ordreSaisons = ['Hiver', 'Printemps', 'Été', 'Automne'];
                    $classesSaisons = [
                        'Hiver' => 'winter',
                        'Printemps' => 'spring',
                        'Été' => 'summer',
                        'Automne' => 'autumn'
                    ];
                    
                    // Réorganiser les données par saison
                    $donneesParSaison = [];
                    foreach ($moyennesSaisonnieres as $cleVariable => $donneesSaison) {
                        foreach ($donneesSaison as $saison => $statistiques) {
                            if (!isset($donneesParSaison[$saison])) {
                                $donneesParSaison[$saison] = [];
                            }
                            $donneesParSaison[$saison][$cleVariable] = $statistiques;
                        }
                    }
                    ?>
                    
                    <?php foreach ($ordreSaisons as $saison): ?>
                        <?php if (isset($donneesParSaison[$saison])): ?>
                            <h3><?= htmlspecialchars($saison) ?></h3>
                            <div class="season-card <?= $classesSaisons[$saison] ?>">
                                <div class="season-metrics">
                                    <?php 
                                    // Ordre des variables à afficher
                                    $ordreVariables = ['so', 'thetao'];
                                    $labelsFrancais = [
                                        'so' => 'Salinité',
                                        'thetao' => 'Température'
                                    ];
                                    
                                    foreach ($ordreVariables as $cleVariable): 
                                        if (isset($donneesParSaison[$saison][$cleVariable])):
                                            $stats = $donneesParSaison[$saison][$cleVariable];
                                    ?>
                                        <div>
                                            <div class="season-metric-label">
                                                <?= htmlspecialchars($labelsFrancais[$cleVariable] ?? $nomsFrancaisVariables[$cleVariable] ?? $cleVariable) ?>
                                            </div>
                                            <div class="season-metric-value">
                                                <?= number_format($stats['moyenne'], 2, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?>
                                            </div>
                                            <div class="season-metric-date">
                                                <?= number_format($stats['nombreMesures'], 0, ',', ' ') ?> mesures
                                            </div>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-column">
            <h2>Export de données</h2>
            <div class="export-section">
                <div class="export-buttons">
                    <form method="GET" action="<?= $baseURL ?>" class="btn btn-csv">
                        <input type="hidden" name="controller" value="point">
                        <input type="hidden" name="action" value="exportCSV">
                        <input type="hidden" name="id" value="<?= intval($id_point ?? 0) ?>">
                        <input type="hidden" name="years" value="<?= intval($nombreAnneesSelectionnees ?? 1) ?>">
                        <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: background-color 0.2s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            Exporter CSV
                        </button>
				    </form>

                    <form method="GET" action="<?= $baseURL ?>" class="btn btn-json">
                        <input type="hidden" name="controller" value="point">
                        <input type="hidden" name="action" value="exportJSON">
                        <input type="hidden" name="id" value="<?= intval($id_point ?? 0) ?>">
                        <input type="hidden" name="years" value="<?= intval($nombreAnneesSelectionnees ?? 1) ?>">
                        <button type="submit" style="padding: 10px 20px; background-color: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: background-color 0.2s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="M10 12h4"></path>
                                <path d="M10 16h4"></path>
                            </svg>
                            Exporter JSON
                        </button>
				    </form>

                    <button class="btn btn-netcdf">Exporter NETCDF</button>
                </div>
            </div>
            
            <h2>Dernière valeur</h2>
            <div class="card data-section">
                <div class="latest-values">
                    <div class="metrics-grid">
                    <?php if (empty($mesuresRecentes)): ?>
                    <p>Aucune mesure récente disponible pour ce point.</p>
                    <?php else: ?>
                        
                        <?php foreach ($mesuresRecentes as $cleVariable => $donneesMesure): ?>
                            <div class="metric">
                                <div class="metric-label"><?= htmlspecialchars($nomsFrancaisVariables[$cleVariable] ?? $labelsFrancais[$cleVariable]) ?></div>
                                <div class="metric-value"><?= number_format($donneesMesure['valeur'], 4, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?></div>
                                <div class="metric-date"><?= htmlspecialchars($donneesMesure['date']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    </div>
                </div>
            </div>


            <h2>Évolution</h2>
            <div class="card evolution-section">
                <div class="evolution-section">
                    <!-- Graphique d'évolution -->
                    <?php if (!empty($donneesAnnuelles)): ?>
                    <div class="chart-container">
                        <canvas id="graphique-evolution"></canvas>
                    </div>

                    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
                    <script>
// Données PHP converties en JavaScript
const donneesAnnuelles = <?= json_encode($donneesAnnuelles) ?>;
const nomsFrancaisVariables = <?= json_encode($nomsFrancaisVariables) ?>;
const unitesVariables = <?= json_encode($unitesVariables) ?>;

const etiquettes = [...new Set(donneesAnnuelles.map(d => d.date))].sort();
const variables = Object.keys(donneesAnnuelles[0]?.valeurs || {});

const contexte = document.getElementById('graphique-evolution').getContext('2d');

// Fonction pour créer un dégradé élégant
function createGradient(ctx, color) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color.replace('rgb', 'rgba').replace(')', ', 0.3)'));
    gradient.addColorStop(1, color.replace('rgb', 'rgba').replace(')', ', 0.0)'));
    return gradient;
}

const couleurs = {
    'so': 'rgb(54, 162, 235)',     // Bleu Océan
    'thetao': 'rgb(255, 99, 132)'  // Corail/Rouge
};

const ensemblesDonnees = variables.map(cleVariable => {
    const color = couleurs[cleVariable] || 'rgb(75, 192, 192)';
    
    return {
        label: nomsFrancaisVariables[cleVariable] || cleVariable,
        data: etiquettes.map(date => {
            const entree = donneesAnnuelles.find(d => d.date === date);
            return entree?.valeurs[cleVariable] ?? null;
        }),
        borderColor: color,
        backgroundColor: createGradient(contexte, color),
        borderWidth: 3,
        fill: true,            // Active le remplissage sous la courbe
        tension: 0.4,          // Rend les lignes courbes (Spline)
        pointRadius: 0,        // Cache les points par défaut
        pointHoverRadius: 6,   // Affiche un gros point au survol
        pointBackgroundColor: color,
        yAxisID: cleVariable
    };
});

const graphique = new Chart(contexte, {
    type: 'line',
    data: {
        labels: etiquettes,
        datasets: ensemblesDonnees
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        padding: 20,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true, // Légende avec des cercles au lieu de carrés
                    padding: 20,
                    font: { size: 13, weight: 'bold' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#333',
                bodyColor: '#666',
                borderColor: '#ddd',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        const cleVariable = context.dataset.yAxisID;
                        const unite = unitesVariables[cleVariable] || '';
                        return ` ${context.dataset.label}: ${context.parsed.y.toFixed(3)} ${unite}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false }, // Supprime les lignes verticales pour un look plus propre
                ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
            },
            so: {
                type: 'linear',
                display: variables.includes('so'),
                position: 'left',
                title: { display: true, text: 'Salinité (PSU)', font: { weight: 'bold' } },
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            thetao: {
                type: 'linear',
                display: variables.includes('thetao'),
                position: 'right',
                title: { display: true, text: 'Température (°C)', font: { weight: 'bold' } },
                grid: { drawOnChartArea: false } // Évite la superposition des grilles
            }
        }
    }
});
</script>
                    <?php endif; ?>
                </div>
            </div>

                <div class="ecartype">
                    <h2>Moyenne</h2>
                    <p class="average-note">L'écart-type (σ) mesure la variabilité : plus il est élevé, plus les valeurs fluctuent.</p>
                </div>
                <div class="card">
                <div class="average-section">
                    <?php if (empty($moyennesAnnuelles)): ?>
                    <p>Aucune donnée disponible pour calculer les moyennes sur cette période.</p>
                    <?php else: ?>
                                <?php foreach ($moyennesAnnuelles as $cleVariable => $statistiques): ?>
                                    <div class="average-metric">
                                        <div class="average-label"><?= htmlspecialchars($nomsFrancaisVariables[$cleVariable] ?? $cleVariable) ?></div>
                                        <div class="average-value"><?= number_format($statistiques['moyenne'], 4, ',', ' ') ?> <?= htmlspecialchars($unitesVariables[$cleVariable] ?? '') ?></div>
                                        <div class="average-box-range">
                                            <div class="average-range">
                                                <span>Min</span>
                                                <span><?= number_format($statistiques['minimum'], 4, ',', ' ') ?></span>
                                            </div>
                                            <div class="average-range">
                                                <span>Max</span>
                                                <span><?= number_format($statistiques['maximum'], 4, ',', ' ') ?></span>
                                            </div>
                                        </div>
                                        <div class="average-stats">Ecart Type : <?= number_format($statistiques['ecartType'], 4, ',', ' ') ?></div>
                                        <div class="average-stats"><?= $statistiques['nombreMesures'] ?> mesures</div>
                                    </div>
                                <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>