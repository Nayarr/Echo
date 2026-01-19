<?php
// Fichier : src/Controller/controllerPoint.php

namespace App\SAE\Controller;

use App\SAE\Model\Repository\PointRepository;
use App\SAE\Config\Conf;

class controllerPoint
{
    // Constructeur vide nécessaire car le frontController envoie $factory
    public function __construct($factory) {}

    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    public static function carte(): void {
        self::afficheVue('point/view.php', [
            "pagetitle" => "Carte des points",
            "cheminVueBody" => "carte.php"
        ]);
    }

    public static function apiNearestPoint(): void {
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