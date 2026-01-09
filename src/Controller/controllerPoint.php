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

        $latitude = floatval($_GET["lat"] ?? 0);
        $longitude = floatval($_GET["lon"] ?? 0);
        $rayon = floatval($_GET["radius"] ?? 8);

        try {
            $depot = new PointRepository();
            $point = $depot->trouverPointLePlusProche($latitude, $longitude, $rayon);

            ob_get_clean();
            echo json_encode($point);
        } catch (\Throwable $exception) {
            ob_get_clean();
            
            // Log l'erreur
            self::enregistrerErreur('apiNearestPoint', $exception, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'rayon' => $rayon
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

        $latitude = $_GET['lat'] ?? null;
        $longitude = $_GET['lon'] ?? null;
        $dateDebut = $_GET['start'] ?? null;
        $dateFin = $_GET['end'] ?? null;
        $nomDataset = $_GET['dataset'] ?? 'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m';
        $variables = $_GET['variables'] ?? 'so,thetao';

        if ($latitude === null || $longitude === null || $dateDebut === null || $dateFin === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing parameters: lat, lon, start, end required"]);
            return;
        }

        try {
            $depot = new PointRepository();
            $resultat = $depot->recupererDonneesCopernicus(
                floatval($latitude),
                floatval($longitude),
                $dateDebut,
                $dateFin,
                $nomDataset,
                $variables
            );

            if (!$resultat['succes']) {
                http_response_code(500);
                echo json_encode(["error" => $resultat['erreur']]);
                return;
            }

            echo json_encode($resultat['donnees']);
        } catch (\Throwable $exception) {
            self::enregistrerErreur('apiCopernicusPoint', $exception, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
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
        $idPoint = intval($_GET['id'] ?? 0);
        $nombreAnneesSelectionnees = intval($_GET['years'] ?? 1);
        
        // Valider le nombre d'années (entre 1 et 10)
        if ($nombreAnneesSelectionnees < 1 || $nombreAnneesSelectionnees > 10) {
            $nombreAnneesSelectionnees = 1;
        }

        // Initialisation des variables pour la vue
        $latitude = 0.0;
        $longitude = 0.0;
        $mesuresRecentes = [];
        $moyennesAnnuelles = [];
        $donneesAnnuelles = [];
        $moyennesSaisonnieres = [];
        $messageErreur = null;
        $dateDebutPeriode = '';
        $dateFinPeriode = '';

        if ($idPoint <= 0) {
            $messageErreur = "ID de point invalide.";
        } else {
            try {
                $depot = new PointRepository();
                $objetPoint = $depot->select((string)$idPoint);
                
                if ($objetPoint === null) {
                    $messageErreur = "Point non trouvé dans la base de données.";
                } else {
                    $latitude = $objetPoint->getLatitude();
                    $longitude = $objetPoint->getLongitude();

                    // 1. Récupérer les données du jour pour les valeurs récentes
                    $aujourdhui = date('Y-m-d');
                    $resultatJour = $depot->recupererDonneesCopernicus(
                        $latitude,
                        $longitude,
                        $aujourdhui,
                        $aujourdhui,
                        'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m',
                        'so,thetao'
                    );

                    if ($resultatJour['succes']) {
                        $mesuresRecentes = $depot->extraireDernieresMesures($resultatJour['donnees']);
                    }

                    // 2. Récupérer et analyser les données sur X années
                    $dateFinPeriode = date('Y-m-d');
                    $dateDebutPeriode = date('Y-m-d', strtotime("-{$nombreAnneesSelectionnees} years"));
                    
                    // Utiliser la méthode façade pour tout récupérer en une fois
                    $analyse = $depot->obtenirAnalysePoint(
                        $latitude,
                        $longitude,
                        $dateDebutPeriode,
                        $dateFinPeriode
                    );

                    if ($analyse['succes']) {
                        $moyennesAnnuelles = $analyse['statistiques'];
                        $donneesAnnuelles = $analyse['donneesGraphique'];
                        $moyennesSaisonnieres = $analyse['moyennesSaisonnieres'];
                    } else {
                        // Si échec uniquement pour la période, on garde les mesures du jour
                        if (empty($mesuresRecentes)) {
                            $messageErreur = "Erreur lors de la récupération des données : " . $analyse['erreur'];
                        }
                    }
                }
            } catch (\Throwable $exception) {
                $messageErreur = "Erreur : " . $exception->getMessage();
                
                self::enregistrerErreur('detail', $exception, [
                    'idPoint' => $idPoint,
                    'nombreAnnees' => $nombreAnneesSelectionnees
                ]);
            }
        }

        // Afficher la vue avec toutes les données
        self::afficheVue('point/view.php', [
            'pagetitle' => 'Détail point',
            'cheminVueBody' => 'detail.php',
            'id_point' => $idPoint,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'mesuresRecentes' => $mesuresRecentes,
            'moyennesAnnuelles' => $moyennesAnnuelles,
            'donneesAnnuelles' => $donneesAnnuelles,
            'moyennesSaisonnieres' => $moyennesSaisonnieres,
            'nombreAnneesSelectionnees' => $nombreAnneesSelectionnees,
            'dateDebutPeriode' => $dateDebutPeriode,
            'dateFinPeriode' => $dateFinPeriode,
            'messageErreur' => $messageErreur
        ]);
    }

    /**
     * Méthode utilitaire pour logger les erreurs
     */
    private static function enregistrerErreur(string $action, \Throwable $exception, array $contexte = []): void
    {
        $dossierLogs = __DIR__ . '/../../var/log';
        if (!is_dir($dossierLogs)) {
            @mkdir($dossierLogs, 0755, true);
        }

        $fichierLog = $dossierLogs . '/controller_errors.log';
        $maintenant = date('Y-m-d H:i:s');
        
        $message = "[$maintenant] Action: $action\n";
        foreach ($contexte as $cle => $valeur) {
            $message .= "$cle: " . print_r($valeur, true) . "\n";
        }
        $message .= "Erreur: " . $exception->getMessage() . "\n";
        $message .= $exception->getTraceAsString() . "\n\n";
        
        @file_put_contents($fichierLog, $message, FILE_APPEND);
    }
}