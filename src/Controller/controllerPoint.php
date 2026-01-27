<?php
// Fichier : src/Controller/controllerPoint.php

namespace App\SAE\Controller;

use App\SAE\Model\DataObject;
use App\SAE\Model\Repository\PointRepository;
use App\SAE\Config\Conf;
use App\SAE\Lib\Session; // Assurez-vous d'utiliser votre classe Session

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
    private $database;

    // 1. On récupère la factory envoyée par le frontController
    public function __construct($factory) {
        // On prépare l'accès à la base de données dès le début
        $this->database = $factory->createDatabase();
    }

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
    
    // MODIFICATION: Gérer les dates personnalisées
    $dateFinPeriode = $_GET['date_fin'] ?? date('Y-m-d');
    $dateDebutPeriode = $_GET['date_debut'] ?? date('Y-m-d', strtotime('-1 month'));
    
    // Valider les dates
    if (strtotime($dateDebutPeriode) > strtotime($dateFinPeriode)) {
        $temp = $dateDebutPeriode;
        $dateDebutPeriode = $dateFinPeriode;
        $dateFinPeriode = $temp;
    }
    
    // Calculer le nombre de jours
    $nombreJours = ceil((strtotime($dateFinPeriode) - strtotime($dateDebutPeriode)) / (60 * 60 * 24));

    // Initialisation des variables pour la vue
    $latitude = 0.0;
    $longitude = 0.0;
    $mesuresRecentes = [];
    $moyennesAnnuelles = [];
    $donneesAnnuelles = [];
    $moyennesSaisonnieres = [];
    $messageErreur = null;

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

                // 2. Récupérer et analyser les données sur la période choisie
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
                    if (empty($mesuresRecentes)) {
                        $messageErreur = "Erreur lors de la récupération des données : " . $analyse['erreur'];
                    }
                }
            }
        } catch (\Throwable $exception) {
            $messageErreur = "Erreur : " . $exception->getMessage();
            
            self::enregistrerErreur('detail', $exception, [
                'idPoint' => $idPoint,
                'dateDebut' => $dateDebutPeriode,
                'dateFin' => $dateFinPeriode
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
        'dateDebutPeriode' => $dateDebutPeriode,
        'dateFinPeriode' => $dateFinPeriode,
        'nombreJours' => $nombreJours,
        'messageErreur' => $messageErreur
    ]);
}

    public static function rechercheParCoordonnees(): void
    {
        $lat = floatval($_GET["lat"] ?? 0);
        $lon = floatval($_GET["lon"] ?? 0);
        $radius = 8; // km

        try {
            $repo = new PointRepository();
            $point = $repo->trouverPointLePlusProche($lat, $lon, $radius);

            if (!$point || !isset($point['id_point'])) {
                // Aucun point trouvé - afficher un message d'erreur
                self::afficheVue('point/view.php', [
                    "pagetitle" => "Point non trouvé",
                    "cheminVueBody" => "erreur_point.php",
                    "message" => "Aucun point trouvé dans un rayon de {$radius} km autour des coordonnées Lat: {$lat}, Lon: {$lon}"
                ]);
                return;
            }

            // Appeler directement la méthode detail en modifiant $_GET
            $_GET['id'] = $point['id_point'];
            self::detail();

        } catch (\Throwable $e) {
            self::afficheVue('point/view.php', [
                "pagetitle" => "Erreur",
                "cheminVueBody" => "erreur_point.php",
                "message" => "Erreur lors de la recherche du point"
            ]);
        }
    }

    /**
 /**
 * Export des données au format CSV
 */
public static function exportCSV(): void
{
    $idPoint = intval($_GET['id'] ?? 0);
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-d', strtotime('-1 month'));
    $dateFin = $_GET['date_fin'] ?? date('Y-m-d');
    
    if ($idPoint <= 0) {
        http_response_code(400);
        echo "ID invalide";
        return;
    }

    // Valider les dates
    if (strtotime($dateDebut) > strtotime($dateFin)) {
        http_response_code(400);
        echo "La date de début ne peut pas être après la date de fin";
        return;
    }

    try {
        $depot = new PointRepository();
        $objetPoint = $depot->select((string)$idPoint);
        
        if (!$objetPoint) {
            http_response_code(404);
            echo "Point non trouvé";
            return;
        }

        $latitude = $objetPoint->getLatitude();
        $longitude = $objetPoint->getLongitude();

        $analyse = $depot->obtenirAnalysePoint($latitude, $longitude, $dateDebut, $dateFin);

        if (!$analyse['succes']) {
            http_response_code(500);
            echo "Erreur lors de la récupération des données";
            return;
        }

        // Générer le CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="point_' . $idPoint . '_' . $dateDebut . '_' . $dateFin . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // En-têtes
        fputcsv($output, ['Date', 'Salinité (PSU)', 'Température (°C)']);
        
        // Données
        foreach ($analyse['donneesGraphique'] as $ligne) {
            fputcsv($output, [
                $ligne['date'],
                $ligne['valeurs']['so'] ?? '',
                $ligne['valeurs']['thetao'] ?? ''
            ]);
        }
        
        fclose($output);
        exit();

    } catch (\Throwable $e) {
        http_response_code(500);
        echo "Erreur: " . $e->getMessage();
    }
}

/**
 * Export des données au format JSON
 */
public static function exportJSON(): void
{
    $idPoint = intval($_GET['id'] ?? 0);
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-d', strtotime('-1 month'));
    $dateFin = $_GET['date_fin'] ?? date('Y-m-d');
    
    if ($idPoint <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID invalide"]);
        return;
    }

    // Valider les dates
    if (strtotime($dateDebut) > strtotime($dateFin)) {
        http_response_code(400);
        echo json_encode(["error" => "La date de début ne peut pas être après la date de fin"]);
        return;
    }

    try {
        $depot = new PointRepository();
        $objetPoint = $depot->select((string)$idPoint);
        
        if (!$objetPoint) {
            http_response_code(404);
            echo json_encode(["error" => "Point non trouvé"]);
            return;
        }

        $latitude = $objetPoint->getLatitude();
        $longitude = $objetPoint->getLongitude();

        $analyse = $depot->obtenirAnalysePoint($latitude, $longitude, $dateDebut, $dateFin);

        if (!$analyse['succes']) {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la récupération des données"]);
            return;
        }

        // Préparer les données JSON
        $export = [
            'metadata' => [
                'point_id' => $idPoint,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'export_date' => date('Y-m-d H:i:s')
            ],
            'statistiques' => $analyse['statistiques'],
            'moyennes_saisonnieres' => $analyse['moyennesSaisonnieres'],
            'donnees' => $analyse['donneesGraphique']
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="point_' . $idPoint . '_' . $dateDebut . '_' . $dateFin . '.json"');
        
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();

    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
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
