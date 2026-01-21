<?php
// Fichier : src/Controller/controllerPoint.php

namespace App\SAE\Controller;

use App\SAE\Model\Repository\PointRepository;
use App\SAE\Config\Conf;
use App\SAE\Lib\Session; // Assurez-vous d'utiliser votre classe Session

class controllerPoint
{
    private $database;

    // 1. On récupère la factory envoyée par le frontController
    public function __construct($factory) {
        // On prépare l'accès à la base de données dès le début
        $this->database = $factory->createDatabase();
    }

    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    // 2. La fonction carte optimisée
    public function carte(): void {
        $favorisList = [];

        // On vérifie la session via $_SESSION directement ou votre classe Session
        if (isset($_SESSION['user_uid'])) {
            try {
                // On utilise la connexion déjà établie dans le constructeur ($this->database)
                // Plus besoin de remettre le chemin du fichier JSON ici !
                $uid = $_SESSION['user_uid'];
                $snapshot = $this->database->getReference('users/' . $uid . '/favoris')->getSnapshot();

                if ($snapshot->exists()) {
                    // On récupère les IDs des points favoris
                    $favorisList = array_keys($snapshot->getValue());
                }
            } catch (\Exception $e) {
                // En cas d'erreur Firebase, on continue sans les favoris (pour ne pas bloquer la carte)
                error_log("Erreur récupération favoris: " . $e->getMessage());
            }
        }

        // Envoi à la vue
        self::afficheVue('point/view.php', [
            "pagetitle" => "Carte des points",
            "cheminVueBody" => "carte.php",
            "favoris" => json_encode($favorisList) // Liste envoyée au JS
        ]);
    }

    public function apiNearestPoint(): void {
        header("Content-Type: application/json");
        $lat = floatval($_GET["lat"] ?? 0);
        $lon = floatval($_GET["lon"] ?? 0);
        $radius = floatval($_GET["radius"] ?? 8);

        $repo = new PointRepository();
        $point = $repo->findNearestPoint($lat, $lon, $radius);

        echo json_encode($point);
    }
}
?>