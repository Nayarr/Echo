<?php

// Namespace du contrôleur Voiture.
// Permet de structurer le projet et d'activer l'autoload PSR-4.
namespace App\SAE\Controller;

// Import des classes nécessaires :
// - l'objet Voiture (le modèle)
// - le repository Voiture (accès à la base)
// - la configuration du site
use App\SAE\Model\DataObject\Point;
use App\SAE\Model\Repository\PointRepository;
use App\SAE\Config\Conf;

class controllerPoint
{

    /**
     * Méthode utilitaire pour afficher une vue du module Voiture.
     * $cheminVue : correspond au fichier de vue à afficher.
     * $parametres : tableau contenant les variables à passer à la vue.
     */
    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        // Ajoute automatiquement la baseURL dans les paramètres transmis à la vue.
        // Cela permet d'avoir des liens fonctionnels dans le menu.
        $parametres['baseURL'] = Conf::getBaseURL();

        // Transforme les clés du tableau en variables (ex : ["voitures" => ...] devient $voitures)
        extract($parametres);

        // Charge la vue principale du module voiture
        require __DIR__ . "/../view/$cheminVue";
    }

    public static function accueil(): void {
        ControllerPoint::afficheVue('accueil/view.php', [
            "pagetitle" => "Accueil",
            "cheminVueBody" => "index.php"
        ]);
    }

    public static function carte(): void {
        ControllerPoint::afficheVue('point/view.php', [
            "pagetitle" => "Carte des points",
            "cheminVueBody" => "carte.php"
        ]);
    }


    public static function apiNearestPoint(): void
    {
        // Empêche l'affichage accidentel (warnings, notices) en tamponnant la sortie
        if (function_exists('ob_start')) {
            ob_start();
        }

        header("Content-Type: application/json; charset=utf-8");

        $lat = floatval($_GET["lat"] ?? 0);
        $lon = floatval($_GET["lon"] ?? 0);
        $radius = floatval($_GET["radius"] ?? 8);

        try {
            $repo = new PointRepository();
            $point = $repo->findNearestPoint($lat, $lon, $radius);

            // Vider le tampon de sortie (warnings, etc.) avant d'envoyer le JSON
            if (function_exists('ob_get_clean')) {
                ob_get_clean();
            }

            echo json_encode($point);
        } catch (\Throwable $e) {
            if (function_exists('ob_get_clean')) {
                ob_get_clean();
            }

            // S'assure que le dossier de logs existe
            $logDir = __DIR__ . '/../../var/log';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }

            // Écrit les détails de l'erreur dans le journal pour le débogage
            $logFile = $logDir . '/api_errors.log';
            $now = date('Y-m-d H:i:s');
            $msg = "[$now] action=apiNearestPoint ";
            $msg .= 'lat=' . ($lat ?? 'n/a') . ' lon=' . ($lon ?? 'n/a') . ' radius=' . ($radius ?? 'n/a') . "\n";
            $msg .= "Error: " . $e->getMessage() . "\n";
            $msg .= $e->getTraceAsString() . "\n\n";
            @file_put_contents($logFile, $msg, FILE_APPEND);

            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
        }
    }

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

        $py = __DIR__ . '/../../scripts/fetch_copernicus.py';
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
        exec($cmd, $output, $ret);

        $full = implode("\n", $output);
        // si le script a renvoyé une erreur JSON, on la propage
        $decoded = json_decode($full, true);
        if ($decoded === null) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to parse python output", "raw" => $full]);
            return;
        }

        echo json_encode($decoded);
    }

    public static function detail(): void
    {
        $id = intval($_GET['id'] ?? 0);

        $latitude = 0.0;
        $longitude = 0.0;

        if ($id > 0) {
            try {
                $repo = new PointRepository();
                $pointObj = $repo->select((string)$id);
                if ($pointObj !== null) {
                    // $pointObj is an instance of Point
                    $latitude = $pointObj->getLatitude();
                    $longitude = $pointObj->getLongitude();
                }
            } catch (\Throwable $e) {
                // ignore and show page without coords
            }
        }

        ControllerPoint::afficheVue('point/view.php', [
            'pagetitle' => 'Détail point',
            'cheminVueBody' => 'detail.php',
            'id_point' => $id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    
}
