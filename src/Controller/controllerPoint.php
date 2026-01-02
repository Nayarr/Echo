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

    
}
