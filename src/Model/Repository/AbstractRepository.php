<?php

// Namespace du dossier contenant les repositories (accès à la base).
namespace App\SAE\Model\Repository;

// Import de la classe mère des objets métiers
use App\SAE\Model\DataObject\AbstractDataObject;

// Import de la connexion PDO (Singleton).
use App\SAE\Model\Repository\DatabaseConnection as DatabaseConnection;


/**
 * Classe abstraite qui factorise toutes les opérations CRUD (Create / Read / Update / Delete).
 *
 * C’est le cœur de la couche "Model" de l’architecture MVC.
 * Toutes les classes Repository (VoitureRepository, UtilisateurRepository, TrajetRepository…)
 * héritent de cette classe pour ne pas réécrire la logique SQL.
 */
abstract class AbstractRepository
{
    /**
     * Méthodes abstraites que chaque repository DOIT implémenter.
     *
     * Elles servent à personnaliser le comportement pour chaque entité (voiture, utilisateur…).
     */

    // Nom de la table (ex : "voiture", "utilisateur", "trajet")
    protected abstract function getNomTable(): string;

    // Nom de la clé primaire (ex : "immat", "login", "id")
    protected abstract function getNomClePrimaire(): string;

    // Liste des colonnes de la table SQL (sauf clé auto-incrémentée si applicable)
    protected abstract function getNomsColonnes(): array;

    // Méthode qui permet de transformer une ligne SQL en objet métier
    protected abstract function construire(array $objetFormatTableau): AbstractDataObject;


    public function selectLimit(int $limit = 10000): array {
            $sql = "SELECT id_point, latitude, longitude FROM Points LIMIT :lim";
            $stmt = DatabaseConnection::getPdo()->prepare($sql);
            $stmt->bindValue(":lim", $limit, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

    /**
     * SELECT * FROM table
     * Récupère TOUTES les lignes de la table.
     */
    public function selectAll(): array {
        // Nom de la table ciblée
        $table = $this->getNomTable();

        // Requête SQL simple
        $sql = "SELECT * FROM $table";

        // Récupération de l'unique connexion PDO (Singleton)
        $pdo = DatabaseConnection::getPdo();

        // Exécution directe comme la requête n'a pas de paramètres
        $statement = $pdo->query($sql);

        // Tableau pour stocker les objets métiers
        $resultats = [];

        // Pour chaque ligne SQL récupérée, on construit un objet métier
        foreach ($statement as $row) {
            // Appel à la méthode abstraite construire() définie dans chaque repository
            $resultats[] = $this->construire($row);
        }

        return $resultats;
    }


    /**
     * SELECT * FROM table WHERE cle = valeur
     * Récupère UNE seule ligne.
     */
    public function select(string $valeurClePrimaire): ?AbstractDataObject {
        $table = $this->getNomTable();
        $cle = $this->getNomClePrimaire();

        // Requête paramétrée → sécurité contre injections SQL
        $sql = "SELECT * from $table WHERE $cle = :cleTag";

        // Prépare la requête
        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        // Paramètres envoyés à la requête
        $values = array(
            "cleTag" => $valeurClePrimaire
        );

        // Exécution avec paramètres
        $pdoStatement->execute($values);

        // fetch() récupère une seule ligne SQL (ou false si rien trouvé)
        $objet = $pdoStatement->fetch();

        // Si aucune ligne correspond → null
        if ($objet == false) {
            return null;
        } else {
            // Sinon on transforme la ligne SQL en objet métier
            return $this->construire($objet);
        }
    }


    /**
     * DELETE FROM table WHERE cle = valeur
     * Supprime un enregistrement.
     */
    public function delete($valeurClePrimaire) : void{
        $table = $this->getNomTable();
        $cle = $this->getNomClePrimaire();

        $sql = "DELETE FROM $table WHERE $cle = :cleTag";

        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        $values = array(
            "cleTag" => $valeurClePrimaire
        );

        // On exécute la requête DELETE
        $pdoStatement->execute($values);
    }


    /**
     * INSERT INTO table (colonnes) VALUES (tags)
     * Ajoute un nouvel objet métier dans la base.
     */
    public function create($objet): void {
        $table = $this->getNomTable();

        // Liste des colonnes de la table (définies par le repository enfant)
        $colonnes = $this->getNomsColonnes();

        // Construction de la chaîne "col1, col2, col3"
        $colonnesString = implode(", ", $colonnes);

        // Construction automatique des tags : ":col1, :col2, :col3"
        $tagsString = implode(", ", array_map(fn($colonne) => ":$colonne", $colonnes));

        // Requête SQL générée dynamiquement
        $sql = "INSERT INTO $table ($colonnesString) VALUES ($tagsString)";

        // Préparation de la requête
        $pdo = DatabaseConnection::getPdo();
        $statement = $pdo->prepare($sql);

        // On récupère les valeurs à insérer via formatTableau()
        $data = $objet->formatTableau();

        // Exécution de l'INSERT
        $statement->execute($data);
    }


    /**
     * UPDATE table SET col=:col WHERE cle=:cle
     * Mets à jour un objet existant.
     */
    public function update($objet): void {
        $table = $this->getNomTable();
        $cle = $this->getNomClePrimaire();

        // Liste des colonnes à mettre à jour
        $colonnes = $this->getNomsColonnes();

        // Construction de la partie SET du SQL
        $ParamSet = [];
        foreach ($colonnes as $colonne) {
            // On évite de mettre la clé primaire dans le SET
            if ($colonne !== $cle) {
                $ParamSet[] = "$colonne = :$colonne";
            }
        }

        // Concaténation avec virgules
        $setString = implode(", ", $ParamSet);

        // Requête SQL dynamique
        $sql = "UPDATE $table SET $setString WHERE $cle = :$cle";

        $pdo = DatabaseConnection::getPdo();
        $statement = $pdo->prepare($sql);

        // formatTableau() fournit les valeurs à mettre à jour
        $data = $objet->formatTableau();

        // Exécution de la requête
        $statement->execute($data);
    }


    /**
     * Variante de create(), fournie dans le TD.
     * Fonctionne de la même manière : INSERT dynamique.
     */
    public function save($objet): void
    {
        $table = $this->getNomTable();
        $colonnes = $this->getNomsColonnes();

        $listeColonnes = implode(", ", $colonnes);
        $listeTags = implode(", ", array_map(fn($colonne) => ":$colonne", $colonnes));

        $sql = "INSERT INTO $table ($listeColonnes) VALUES ($listeTags)";

        $pdoStatement = DatabaseConnection::getPdo()->prepare($sql);

        $valeurs = $objet->formatTableau();

        $pdoStatement->execute($valeurs);
    }

}
