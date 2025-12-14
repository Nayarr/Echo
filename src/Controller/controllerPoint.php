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

    public static function carte() {
        ControllerPoint::afficheVue("point/view.php", [
            "pagetitle" => "Carte",
            "cheminVueBody" => "carte.php"
        ]);
    }


    public static function apiPoints(): void
    {
        header("Content-Type: application/json");

        $zoom = isset($_GET["zoom"]) ? intval($_GET["zoom"]) : 2;

        // LOD simple
        $step = 500;
        if ($zoom >= 4) $step = 100;
        if ($zoom >= 6) $step = 20;
        if ($zoom >= 8) $step = 1;

        // limite de sécurité
        $limit = 200000;

        $repo = new PointRepository();
        $points = $repo->selectLOD($step, $limit);

        echo json_encode($points);
    }





}

?>
