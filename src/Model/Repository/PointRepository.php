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
    public function construire(array $ligneTableau): Point
    {
        return new Point(
            $ligneTableau["id_point"],
            $ligneTableau["latitude"],
            $ligneTableau["longitude"],
            $ligneTableau["geom"]
        );
    }

    /**
     * Trouve le point le plus proche dans un rayon donné.
     * 
     * @param float $latitude Latitude de référence
     * @param float $longitude Longitude de référence
     * @param float $rayonKm Rayon de recherche en km
     * @return array|null Point trouvé avec sa distance, ou null
     */
    public function trouverPointLePlusProche(float $latitude, float $longitude, float $rayonKm = 50): ?array
    {
        $pdo = DatabaseConnection::getPdo();

        // Formule de Haversine pour trouver le point le plus proche dans le rayon
        $requeteSQL = "
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
                ) <= :rayon
            ORDER BY distance ASC
            LIMIT 1
        ";

        $statement = $pdo->prepare($requeteSQL);
        $statement->bindValue(':lat', $latitude);
        $statement->bindValue(':lon', $longitude);
        $statement->bindValue(':rayon', $rayonKm);
        $statement->execute();

        $ligne = $statement->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            // S'assure que la distance est un float
            $ligne['distance'] = floatval($ligne['distance']);
            $ligne['latitude'] = floatval($ligne['latitude']);
            $ligne['longitude'] = floatval($ligne['longitude']);
        }

        return $ligne ?: null;
    }

    // =========================================================================
    // SECTION : RÉCUPÉRATION DONNÉES COPERNICUS
    // =========================================================================

    /**
     * Récupère les données Copernicus via le script Python
     * 
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param string $dateDebut Date de début (YYYY-MM-DD)
     * @param string $dateFin Date de fin (YYYY-MM-DD)
     * @param string $nomDataset ID du dataset Copernicus
     * @param string $variables Variables séparées par des virgules (ex: 'so,thetao')
     * @return array Tableau associatif avec 'succes' (bool), 'donnees' (array) ou 'erreur' (string)
     */
    public function recupererDonneesCopernicus(
        float $latitude, 
        float $longitude, 
        string $dateDebut, 
        string $dateFin, 
        string $nomDataset = 'cmems_mod_glo_phy_anfc_0.083deg_PT1H-m',
        string $variables = 'so,thetao'
    ): array {
        $cheminScriptPython = __DIR__ . '/../../../scripts/fetch_copernicus.py';
        
        // Vérifier que le script Python existe
        if (!file_exists($cheminScriptPython)) {
            return [
                'succes' => false,
                'erreur' => 'Script Python introuvable: ' . $cheminScriptPython
            ];
        }

        // Détection de la commande Python selon l'OS
        $commandePython = 'python3'; // Par défaut pour Linux/Mac
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Sur Windows, essayer 'python' d'abord
            $commandePython = 'python';
        }

        // MODE DEBUG : Vérifier quel Python est utilisé
        exec($commandePython . ' --version 2>&1', $sortieVersion);
        exec($commandePython . ' -c "import sys; print(sys.executable)" 2>&1', $sortieChemin);
        
        $informationsDebug = [
            'version_python' => implode(' ', $sortieVersion),
            'chemin_python' => implode(' ', $sortieChemin),
            'systeme_exploitation' => PHP_OS,
            'commande' => $commandePython
        ];

        $commande = escapeshellcmd($commandePython) . ' ' . escapeshellarg($cheminScriptPython)
            . ' --lat ' . escapeshellarg($latitude)
            . ' --lon ' . escapeshellarg($longitude)
            . ' --start ' . escapeshellarg($dateDebut)
            . ' --end ' . escapeshellarg($dateFin)
            . ' --dataset ' . escapeshellarg($nomDataset)
            . ' --variables ' . escapeshellarg($variables);

        // Exécute le script Python et capture la sortie
        $sortie = null;
        $codeRetour = null;
        exec($commande . ' 2>&1', $sortie, $codeRetour);

        $sortieComplete = implode("\n", $sortie);
        
        // Filtrer les lignes INFO pour ne garder que le JSON
        $lignes = explode("\n", $sortieComplete);
        $lignesJSON = [];
        foreach ($lignes as $ligne) {
            // Ignorer les lignes qui commencent par INFO ou WARNING
            if (!preg_match('/^(INFO|WARNING|DEBUG|ERROR)\s*-/', trim($ligne))) {
                $lignesJSON[] = $ligne;
            }
        }
        $sortieJSON = implode("\n", $lignesJSON);
        
        // Tenter de décoder le JSON
        $donneesDecodees = json_decode($sortieJSON, true);
        
        if ($donneesDecodees === null) {
            return [
                'succes' => false,
                'erreur' => 'Erreur lors du décodage JSON. Debug: ' . json_encode($informationsDebug) . ' | Sortie: ' . substr($sortieJSON, 0, 500)
            ];
        }

        // Vérifier si c'est une erreur renvoyée par le script Python
        if (isset($donneesDecodees['error'])) {
            return [
                'succes' => false,
                'erreur' => $donneesDecodees['error'] . ' | Debug: ' . json_encode($informationsDebug)
            ];
        }

        return [
            'succes' => true,
            'donnees' => $donneesDecodees
        ];
    }

    // =========================================================================
    // SECTION : EXTRACTION ET TRANSFORMATION DES DONNÉES
    // =========================================================================

    /**
     * Extrait les dernières mesures non-nulles de chaque variable
     * 
     * @param array $donnees Tableau de données Copernicus
     * @return array Tableau associatif [variable => ['date' => ..., 'valeur' => ...]]
     */
    public function extraireDernieresMesures(array $donnees): array {
        // Trier par date décroissante pour prendre les plus récentes
        usort($donnees, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        $dernieresMesures = [];
        
        foreach ($donnees as $ligne) {
            $valeurs = $ligne['values'] ?? [];
            
            foreach ($valeurs as $cleVariable => $valeur) {
                // Si la variable n'a pas encore été trouvée et la valeur n'est pas nulle
                if (!isset($dernieresMesures[$cleVariable]) && $valeur !== null) {
                    $dernieresMesures[$cleVariable] = [
                        'date' => $ligne['date'],
                        'valeur' => floatval($valeur)
                    ];
                }
            }
        }

        return $dernieresMesures;
    }

    /**
     * Prépare les données pour le graphique (agrégation par jour)
     * 
     * @param array $donnees Tableau de données Copernicus
     * @return array Tableau trié par date avec moyennes journalières
     */
    public function preparerDonneesGraphique(array $donnees): array {
        // Regrouper par date et calculer la moyenne journalière
        $donneesParDate = [];
        
        foreach ($donnees as $ligne) {
            $date = $ligne['date'];
            $valeurs = $ligne['values'] ?? [];
            
            if (!isset($donneesParDate[$date])) {
                $donneesParDate[$date] = [
                    'date' => $date,
                    'valeurs' => [],
                    'compteurs' => []
                ];
            }
            
            foreach ($valeurs as $cleVariable => $valeur) {
                if ($valeur !== null) {
                    if (!isset($donneesParDate[$date]['valeurs'][$cleVariable])) {
                        $donneesParDate[$date]['valeurs'][$cleVariable] = 0;
                        $donneesParDate[$date]['compteurs'][$cleVariable] = 0;
                    }
                    $donneesParDate[$date]['valeurs'][$cleVariable] += floatval($valeur);
                    $donneesParDate[$date]['compteurs'][$cleVariable]++;
                }
            }
        }
        
        // Calculer les moyennes
        $donneesGraphique = [];
        foreach ($donneesParDate as $date => $informations) {
            $valeursMoyennes = [];
            foreach ($informations['valeurs'] as $cleVariable => $somme) {
                $compteur = $informations['compteurs'][$cleVariable];
                $valeursMoyennes[$cleVariable] = $compteur > 0 ? $somme / $compteur : null;
            }
            
            $donneesGraphique[] = [
                'date' => $date,
                'valeurs' => $valeursMoyennes
            ];
        }
        
        // Trier par date
        usort($donneesGraphique, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        return $donneesGraphique;
    }

    // =========================================================================
    // SECTION : CALCULS STATISTIQUES
    // =========================================================================

    /**
     * Calcule les statistiques globales (moyenne, min, max, écart-type) pour chaque variable
     * 
     * @param array $donnees Tableau de données Copernicus
     * @return array Tableau associatif [variable => ['moyenne' => ..., 'minimum' => ..., 'maximum' => ..., 'ecartType' => ...]]
     */
    public function calculerStatistiques(array $donnees): array {
        $statistiques = [];
        
        // Collecter toutes les valeurs par variable
        $valeursParVariable = [];
        
        foreach ($donnees as $ligne) {
            $valeurs = $ligne['values'] ?? [];
            
            foreach ($valeurs as $cleVariable => $valeur) {
                if ($valeur !== null) {
                    if (!isset($valeursParVariable[$cleVariable])) {
                        $valeursParVariable[$cleVariable] = [];
                    }
                    $valeursParVariable[$cleVariable][] = floatval($valeur);
                }
            }
        }
        
        // Calculer les statistiques pour chaque variable
        foreach ($valeursParVariable as $cleVariable => $valeurs) {
            if (count($valeurs) > 0) {
                $moyenne = array_sum($valeurs) / count($valeurs);
                
                // Calcul de l'écart-type
                $variance = 0;
                foreach ($valeurs as $valeur) {
                    $variance += pow($valeur - $moyenne, 2);
                }
                $ecartType = sqrt($variance / count($valeurs));
                
                $statistiques[$cleVariable] = [
                    'moyenne' => $moyenne,
                    'minimum' => min($valeurs),
                    'maximum' => max($valeurs),
                    'ecartType' => $ecartType,
                    'nombreMesures' => count($valeurs)
                ];
            }
        }
        
        return $statistiques;
    }

    /**
     * Calcule les moyennes saisonnières pour chaque variable
     * 
     * @param array $donnees Tableau de données Copernicus
     * @return array Tableau associatif [variable => [saison => ['moyenne' => ..., 'minimum' => ..., 'maximum' => ...]]]
     */
    public function calculerMoyennesSaisonnieres(array $donnees): array {
        $donneesSaisonnieres = [];
        
        // Définir les saisons (hémisphère nord)
        $saisons = [
            'Hiver' => [12, 1, 2],
            'Printemps' => [3, 4, 5],
            'Été' => [6, 7, 8],
            'Automne' => [9, 10, 11]
        ];
        
        // Collecter les valeurs par saison et par variable
        $valeursParSaison = [];
        
        foreach ($donnees as $ligne) {
            $date = $ligne['date'];
            $mois = intval(date('n', strtotime($date)));
            $valeurs = $ligne['values'] ?? [];
            
            // Déterminer la saison
            $saisonActuelle = '';
            foreach ($saisons as $nomSaison => $moisSaison) {
                if (in_array($mois, $moisSaison)) {
                    $saisonActuelle = $nomSaison;
                    break;
                }
            }
            
            if (empty($saisonActuelle)) continue;
            
            foreach ($valeurs as $cleVariable => $valeur) {
                if ($valeur !== null) {
                    if (!isset($valeursParSaison[$cleVariable])) {
                        $valeursParSaison[$cleVariable] = [];
                    }
                    if (!isset($valeursParSaison[$cleVariable][$saisonActuelle])) {
                        $valeursParSaison[$cleVariable][$saisonActuelle] = [];
                    }
                    $valeursParSaison[$cleVariable][$saisonActuelle][] = floatval($valeur);
                }
            }
        }
        
        // Calculer les statistiques par saison
        foreach ($valeursParSaison as $cleVariable => $valeursSaison) {
            $donneesSaisonnieres[$cleVariable] = [];
            
            foreach ($valeursSaison as $saison => $valeurs) {
                if (count($valeurs) > 0) {
                    $donneesSaisonnieres[$cleVariable][$saison] = [
                        'moyenne' => array_sum($valeurs) / count($valeurs),
                        'minimum' => min($valeurs),
                        'maximum' => max($valeurs),
                        'nombreMesures' => count($valeurs)
                    ];
                }
            }
        }
        
        return $donneesSaisonnieres;
    }

    // =========================================================================
    // SECTION : MÉTHODES DE HAUT NIVEAU (FAÇADE)
    // =========================================================================

    /**
     * Récupère toutes les données d'analyse pour un point sur une période donnée
     * Méthode pratique qui combine toutes les opérations
     * 
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param string $dateDebut Date de début
     * @param string $dateFin Date de fin
     * @return array Tableau complet avec toutes les analyses
     */
    public function obtenirAnalysePoint(
        float $latitude,
        float $longitude,
        string $dateDebut,
        string $dateFin
    ): array {
        $resultat = [
            'succes' => false,
            'dernieresMesures' => [],
            'statistiques' => [],
            'moyennesSaisonnieres' => [],
            'donneesGraphique' => [],
            'erreur' => null
        ];

        // Récupérer les données brutes
        $resultatRecuperation = $this->recupererDonneesCopernicus($latitude, $longitude, $dateDebut, $dateFin);
        
        if (!$resultatRecuperation['succes']) {
            $resultat['erreur'] = $resultatRecuperation['erreur'];
            return $resultat;
        }

        $donnees = $resultatRecuperation['donnees'];

        // Calculer toutes les analyses
        $resultat['succes'] = true;
        $resultat['dernieresMesures'] = $this->extraireDernieresMesures($donnees);
        $resultat['statistiques'] = $this->calculerStatistiques($donnees);
        $resultat['moyennesSaisonnieres'] = $this->calculerMoyennesSaisonnieres($donnees);
        $resultat['donneesGraphique'] = $this->preparerDonneesGraphique($donnees);

        return $resultat;
    }
}