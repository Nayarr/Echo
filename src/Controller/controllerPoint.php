<?php

namespace App\SAE\Controller;

use App\SAE\Model\DataObject;
use App\SAE\Model\Repository\PointRepository;
use App\SAE\Config\Conf;

/**
 * Contrôleur pour les points océanographiques.
 * 
 * Responsabilités :
 *  - Routing : diriger les requêtes vers les bonnes vues
 *  - Coordination : appeler le repository et passer les données aux vues
 *  - Pas de logique métier (déléguée au repository)
 */
class controllerPoint
{

    /**
     * Affiche une vue avec les paramètres fournis
     */
    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    /**
     * Page d'accueil
     */
    public static function accueil(): void {
        self::afficheVue('accueil/view.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "index.php"
        ]);
    }

    /**
     * Page carte des points
     */
    public static function carte(): void {
        self::afficheVue('point/view.php', [
            "pagetitle" => "Carte des points",
            "cheminVueBody" => "carte.php"
        ]);
    }

    /**
     * API : Trouve le point le plus proche
     */
    public static function apiNearestPoint(): void
    {
        ob_start();
        header("Content-Type: application/json; charset=utf-8");

        $lat = floatval($_GET["lat"] ?? 0);
        $lon = floatval($_GET["lon"] ?? 0);
        $radius = floatval($_GET["radius"] ?? 8);

        try {
            $repo = new PointRepository();
            $point = $repo->findNearestPoint($lat, $lon, $radius);

            ob_get_clean();
            echo json_encode($point);
        } catch (\Throwable $e) {
            ob_get_clean();
            
            // Log l'erreur
            self::logError('apiNearestPoint', $e, [
                'lat' => $lat,
                'lon' => $lon,
                'radius' => $radius
            ]);

            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
        }
    }

    /**
     * API : Récupère les données Copernicus pour un point
     */
    public static function apiCopernicusPoint(): void
    {
        header("Content-Type: application/json; charset=utf-8");

        $lat = $_GET['lat'] ?? null;
        $lon = $_GET['lon'] ?? null;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $dataset = $_GET['dataset'] ?? 'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m';
        $variables = $_GET['variables'] ?? 'so,thetao';

        if ($lat === null || $lon === null || $start === null || $end === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing parameters: lat, lon, start, end required"]);
            return;
        }

        try {
            $repo = new PointRepository();
            $result = $repo->fetchCopernicusData(
                floatval($lat),
                floatval($lon),
                $start,
                $end,
                $dataset,
                $variables
            );

            if (!$result['success']) {
                http_response_code(500);
                echo json_encode(["error" => $result['error']]);
                return;
            }

            echo json_encode($result['data']);
        } catch (\Throwable $e) {
            self::logError('apiCopernicusPoint', $e, [
                'lat' => $lat,
                'lon' => $lon,
                'start' => $start,
                'end' => $end
            ]);

            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
        }
    }

    /**
     * Page de détail d'un point avec analyses complètes
     */
    public static function detail(): void
    {
        $id = intval($_GET['id'] ?? 0);
        $selected_years = intval($_GET['years'] ?? 1);
        
        // Valider le nombre d'années (entre 1 et 10)
        if ($selected_years < 1 || $selected_years > 10) {
            $selected_years = 1;
        }

        // Initialisation des variables pour la vue
        $latitude = 0.0;
        $longitude = 0.0;
        $measurements = [];
        $yearly_averages = [];
        $yearly_data = [];
        $seasonal_averages = [];
        $error_message = null;
        $period_start = '';
        $period_end = '';

        if ($id <= 0) {
            $error_message = "ID de point invalide.";
        } else {
            try {
                $repo = new PointRepository();
                $pointObj = $repo->select((string)$id);
                
                if ($pointObj === null) {
                    $error_message = "Point non trouvé dans la base de données.";
                } else {
                    $latitude = $pointObj->getLatitude();
                    $longitude = $pointObj->getLongitude();

                    // 1. Récupérer les données du jour pour les valeurs récentes
                    $today = date('Y-m-d');
                    $resultToday = $repo->fetchCopernicusData(
                        $latitude,
                        $longitude,
                        $today,
                        $today,
                        'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m',
                        'so,thetao'
                    );

                    if ($resultToday['success']) {
                        $measurements = $repo->extractLatestMeasurements($resultToday['data']);
                    }

                    // 2. Récupérer et analyser les données sur X années
                    $endOfPeriod = date('Y-m-d');
                    $startOfPeriod = date('Y-m-d', strtotime("-{$selected_years} years"));
                    
                    $period_start = $startOfPeriod;
                    $period_end = $endOfPeriod;
                    
                    // Utiliser la méthode façade pour tout récupérer en une fois
                    $analysis = $repo->getPointAnalysis(
                        $latitude,
                        $longitude,
                        $startOfPeriod,
                        $endOfPeriod
                    );

                    if ($analysis['success']) {
                        $yearly_averages = $analysis['statistics'];
                        $yearly_data = $analysis['chart_data'];
                        $seasonal_averages = $analysis['seasonal_averages'];
                    } else {
                        // Si échec uniquement pour la période, on garde les mesures du jour
                        if (empty($measurements)) {
                            $error_message = "Erreur lors de la récupération des données : " . $analysis['error'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                $error_message = "Erreur : " . $e->getMessage();
                
                self::logError('detail', $e, [
                    'id' => $id,
                    'years' => $selected_years
                ]);
            }
        }

        // Afficher la vue avec toutes les données
        self::afficheVue('point/view.php', [
            'pagetitle' => 'Détail point',
            'cheminVueBody' => 'detail.php',
            'id_point' => $id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'measurements' => $measurements,
            'yearly_averages' => $yearly_averages,
            'yearly_data' => $yearly_data,
            'seasonal_averages' => $seasonal_averages,
            'selected_years' => $selected_years,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'error_message' => $error_message
        ]);
    }

    /**
     * Méthode utilitaire pour logger les erreurs
     */
    private static function logError(string $action, \Throwable $e, array $context = []): void
    {
        $logDir = __DIR__ . '/../../var/log';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/controller_errors.log';
        $now = date('Y-m-d H:i:s');
        
        $msg = "[$now] Action: $action\n";
        foreach ($context as $key => $value) {
            $msg .= "$key: " . print_r($value, true) . "\n";
        }
        $msg .= "Error: " . $e->getMessage() . "\n";
        $msg .= $e->getTraceAsString() . "\n\n";
        
        @file_put_contents($logFile, $msg, FILE_APPEND);
    }
}