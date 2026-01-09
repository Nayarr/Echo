<?php

// Namespace correspondant au dossier "Model/Repository"
namespace App\SAE\Model\Repository;

use PDO;

// Import de la classe métier Point
use App\SAE\Model\DataObject\Point;

// Import de DatabaseConnection
use App\SAE\Model\Repository\DatabaseConnection as DatabaseConnection;

/**
 * Repository dédié à l'entité Point.
 *
 * Gère toutes les opérations liées aux points :
 *  - Accès base de données
 *  - Récupération données Copernicus
 *  - Calculs statistiques
 */
class PointRepository extends AbstractRepository
{

    /**
     * Indique le nom exact de la table SQL associée.
     */
    protected function getNomTable(): string {
        return "points";
    }

    /**
     * Indique le nom de la clé primaire de la table.
     */
    protected function getNomClePrimaire(): string{
        return "id_point";
    }

    /**
     * Retourne la liste des colonnes SQL utilisées pour :
     *  - les INSERT
     *  - les UPDATE
     */
    protected function getNomsColonnes(): array {
        return ["latitude", "longitude", "geom"];
    }

    /**
     * Méthode essentielle : transforme une ligne SQL en objet Point.
     */
    public function construire(array $row): Point
    {
        return new Point(
            $row["id_point"],
            $row["latitude"],
            $row["longitude"],
            $row["geom"]
        );
    }

    /**
     * Trouve le point le plus proche dans un rayon donné.
     * 
     * @param float $lat Latitude de référence
     * @param float $lon Longitude de référence
     * @param float $radiusKm Rayon de recherche en km
     * @return array|null Point trouvé avec sa distance, ou null
     */
    public function findNearestPoint(float $lat, float $lon, float $radiusKm = 50): ?array
    {
        $pdo = DatabaseConnection::getPdo();

        // Formule de Haversine pour trouver le point le plus proche dans le rayon
        $sql = "
            SELECT
                id_point,
                latitude,
                longitude,
                (
                  6371 * acos(
                    cos(radians(:lat))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(:lon))
                    + sin(radians(:lat)) * sin(radians(latitude))
                  )
                ) AS distance
            FROM Points
            WHERE (
                  6371 * acos(
                    cos(radians(:lat))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(:lon))
                    + sin(radians(:lat)) * sin(radians(latitude))
                  )
                ) <= :radius
            ORDER BY distance ASC
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':lat', $lat);
        $stmt->bindValue(':lon', $lon);
        $stmt->bindValue(':radius', $radiusKm);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // S'assure que la distance est un float
            $row['distance'] = floatval($row['distance']);
            $row['latitude'] = floatval($row['latitude']);
            $row['longitude'] = floatval($row['longitude']);
        }

        return $row ?: null;
    }

    // =========================================================================
    // SECTION : RÉCUPÉRATION DONNÉES COPERNICUS
    // =========================================================================

    /**
     * Récupère les données Copernicus via le script Python
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param string $start Date de début (YYYY-MM-DD)
     * @param string $end Date de fin (YYYY-MM-DD)
     * @param string $dataset ID du dataset Copernicus
     * @param string $variables Variables séparées par des virgules (ex: 'so,thetao')
     * @return array Tableau associatif avec 'success' (bool), 'data' (array) ou 'error' (string)
     */
    public function fetchCopernicusData(
        float $lat, 
        float $lon, 
        string $start, 
        string $end, 
        string $dataset = 'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m',
        string $variables = 'so,thetao'
    ): array {
        $py = __DIR__ . '/../../../scripts/fetch_copernicus.py';
        
        // Vérifier que le script Python existe
        if (!file_exists($py)) {
            return [
                'success' => false,
                'error' => 'Script Python introuvable: ' . $py
            ];
        }

        $cmd = escapeshellcmd("python3") . ' ' . escapeshellarg($py)
            . ' --lat ' . escapeshellarg($lat)
            . ' --lon ' . escapeshellarg($lon)
            . ' --start ' . escapeshellarg($start)
            . ' --end ' . escapeshellarg($end)
            . ' --dataset ' . escapeshellarg($dataset)
            . ' --variables ' . escapeshellarg($variables);

        // Exécute le script Python et capture la sortie
        $output = null;
        $ret = null;
        exec($cmd . ' 2>&1', $output, $ret);

        $full = implode("\n", $output);
        
        // Filtrer les lignes INFO pour ne garder que le JSON
        $lines = explode("\n", $full);
        $jsonLines = [];
        foreach ($lines as $line) {
            // Ignorer les lignes qui commencent par INFO ou WARNING
            if (!preg_match('/^(INFO|WARNING|DEBUG|ERROR)\s*-/', trim($line))) {
                $jsonLines[] = $line;
            }
        }
        $jsonOutput = implode("\n", $jsonLines);
        
        // Tenter de décoder le JSON
        $decoded = json_decode($jsonOutput, true);
        
        if ($decoded === null) {
            return [
                'success' => false,
                'error' => 'Erreur lors du décodage JSON. Sortie filtrée: ' . substr($jsonOutput, 0, 500)
            ];
        }

        // Vérifier si c'est une erreur renvoyée par le script Python
        if (isset($decoded['error'])) {
            return [
                'success' => false,
                'error' => $decoded['error']
            ];
        }

        return [
            'success' => true,
            'data' => $decoded
        ];
    }

    // =========================================================================
    // SECTION : EXTRACTION ET TRANSFORMATION DES DONNÉES
    // =========================================================================

    /**
     * Extrait les dernières mesures non-nulles de chaque variable
     * 
     * @param array $data Tableau de données Copernicus
     * @return array Tableau associatif [variable => ['date' => ..., 'value' => ...]]
     */
    public function extractLatestMeasurements(array $data): array {
        // Trier par date décroissante pour prendre les plus récentes
        usort($data, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        $latest = [];
        
        foreach ($data as $row) {
            $values = $row['values'] ?? [];
            
            foreach ($values as $varKey => $value) {
                // Si la variable n'a pas encore été trouvée et la valeur n'est pas nulle
                if (!isset($latest[$varKey]) && $value !== null) {
                    $latest[$varKey] = [
                        'date' => $row['date'],
                        'value' => floatval($value)
                    ];
                }
            }
        }

        return $latest;
    }

    /**
     * Prépare les données pour le graphique (agrégation par jour)
     * 
     * @param array $data Tableau de données Copernicus
     * @return array Tableau trié par date avec moyennes journalières
     */
    public function prepareChartData(array $data): array {
        // Regrouper par date et calculer la moyenne journalière
        $byDate = [];
        
        foreach ($data as $row) {
            $date = $row['date'];
            $values = $row['values'] ?? [];
            
            if (!isset($byDate[$date])) {
                $byDate[$date] = [
                    'date' => $date,
                    'values' => [],
                    'counts' => []
                ];
            }
            
            foreach ($values as $varKey => $value) {
                if ($value !== null) {
                    if (!isset($byDate[$date]['values'][$varKey])) {
                        $byDate[$date]['values'][$varKey] = 0;
                        $byDate[$date]['counts'][$varKey] = 0;
                    }
                    $byDate[$date]['values'][$varKey] += floatval($value);
                    $byDate[$date]['counts'][$varKey]++;
                }
            }
        }
        
        // Calculer les moyennes
        $chartData = [];
        foreach ($byDate as $date => $info) {
            $avgValues = [];
            foreach ($info['values'] as $varKey => $sum) {
                $count = $info['counts'][$varKey];
                $avgValues[$varKey] = $count > 0 ? $sum / $count : null;
            }
            
            $chartData[] = [
                'date' => $date,
                'values' => $avgValues
            ];
        }
        
        // Trier par date
        usort($chartData, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        return $chartData;
    }

    // =========================================================================
    // SECTION : CALCULS STATISTIQUES
    // =========================================================================

    /**
     * Calcule les statistiques globales (moyenne, min, max, écart-type) pour chaque variable
     * 
     * @param array $data Tableau de données Copernicus
     * @return array Tableau associatif [variable => ['avg' => ..., 'min' => ..., 'max' => ..., 'std' => ...]]
     */
    public function calculateStatistics(array $data): array {
        $stats = [];
        
        // Collecter toutes les valeurs par variable
        $valuesByVar = [];
        
        foreach ($data as $row) {
            $values = $row['values'] ?? [];
            
            foreach ($values as $varKey => $value) {
                if ($value !== null) {
                    if (!isset($valuesByVar[$varKey])) {
                        $valuesByVar[$varKey] = [];
                    }
                    $valuesByVar[$varKey][] = floatval($value);
                }
            }
        }
        
        // Calculer les statistiques pour chaque variable
        foreach ($valuesByVar as $varKey => $values) {
            if (count($values) > 0) {
                $avg = array_sum($values) / count($values);
                
                // Calcul de l'écart-type
                $variance = 0;
                foreach ($values as $val) {
                    $variance += pow($val - $avg, 2);
                }
                $std = sqrt($variance / count($values));
                
                $stats[$varKey] = [
                    'avg' => $avg,
                    'min' => min($values),
                    'max' => max($values),
                    'std' => $std,
                    'count' => count($values)
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Calcule les moyennes saisonnières pour chaque variable
     * 
     * @param array $data Tableau de données Copernicus
     * @return array Tableau associatif [variable => [season => ['avg' => ..., 'min' => ..., 'max' => ...]]]
     */
    public function calculateSeasonalAverages(array $data): array {
        $seasonalData = [];
        
        // Définir les saisons (hémisphère nord)
        $seasons = [
            'Hiver' => [12, 1, 2],
            'Printemps' => [3, 4, 5],
            'Été' => [6, 7, 8],
            'Automne' => [9, 10, 11]
        ];
        
        // Collecter les valeurs par saison et par variable
        $valuesBySeason = [];
        
        foreach ($data as $row) {
            $date = $row['date'];
            $month = intval(date('n', strtotime($date)));
            $values = $row['values'] ?? [];
            
            // Déterminer la saison
            $currentSeason = '';
            foreach ($seasons as $seasonName => $months) {
                if (in_array($month, $months)) {
                    $currentSeason = $seasonName;
                    break;
                }
            }
            
            if (empty($currentSeason)) continue;
            
            foreach ($values as $varKey => $value) {
                if ($value !== null) {
                    if (!isset($valuesBySeason[$varKey])) {
                        $valuesBySeason[$varKey] = [];
                    }
                    if (!isset($valuesBySeason[$varKey][$currentSeason])) {
                        $valuesBySeason[$varKey][$currentSeason] = [];
                    }
                    $valuesBySeason[$varKey][$currentSeason][] = floatval($value);
                }
            }
        }
        
        // Calculer les statistiques par saison
        foreach ($valuesBySeason as $varKey => $seasonValues) {
            $seasonalData[$varKey] = [];
            
            foreach ($seasonValues as $season => $values) {
                if (count($values) > 0) {
                    $seasonalData[$varKey][$season] = [
                        'avg' => array_sum($values) / count($values),
                        'min' => min($values),
                        'max' => max($values),
                        'count' => count($values)
                    ];
                }
            }
        }
        
        return $seasonalData;
    }

    // =========================================================================
    // SECTION : MÉTHODES DE HAUT NIVEAU (FAÇADE)
    // =========================================================================

    /**
     * Récupère toutes les données d'analyse pour un point sur une période donnée
     * Méthode pratique qui combine toutes les opérations
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param string $startDate Date de début
     * @param string $endDate Date de fin
     * @return array Tableau complet avec toutes les analyses
     */
    public function getPointAnalysis(
        float $lat,
        float $lon,
        string $startDate,
        string $endDate
    ): array {
        $result = [
            'success' => false,
            'latest_measurements' => [],
            'statistics' => [],
            'seasonal_averages' => [],
            'chart_data' => [],
            'error' => null
        ];

        // Récupérer les données brutes
        $fetchResult = $this->fetchCopernicusData($lat, $lon, $startDate, $endDate);
        
        if (!$fetchResult['success']) {
            $result['error'] = $fetchResult['error'];
            return $result;
        }

        $data = $fetchResult['data'];

        // Calculer toutes les analyses
        $result['success'] = true;
        $result['latest_measurements'] = $this->extractLatestMeasurements($data);
        $result['statistics'] = $this->calculateStatistics($data);
        $result['seasonal_averages'] = $this->calculateSeasonalAverages($data);
        $result['chart_data'] = $this->prepareChartData($data);

        return $result;
    }
}