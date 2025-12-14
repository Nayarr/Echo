<?php


// Namespace correspondant au dossier "Model/Repository"
namespace App\SAE\Model\Repository;

use PDO;

// Import de la classe métier Voiture
use App\SAE\Model\DataObject\Point;

// Import de DatabaseConnection (utile pour les méthodes manuelles commentées)
use App\SAE\Model\Repository\DatabaseConnection as DatabaseConnection;


/**
 * Repository dédié à l’entité Voiture.
 *
 * Il hérite de AbstractRepository, ce qui lui donne :
 *  - create()
 *  - update()
 *  - delete()
 *  - select()
 *  - selectAll()
 *
 * Ici, on définit uniquement ce qui est propre à la table voiture.
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
     * Ici : id_point.
     */
    protected function getNomClePrimaire(): string{
        return "id_point";
    }

    /**
     * Retourne la liste des colonnes SQL utilisées pour :
     *  - les INSERT
     *  - les UPDATE
     *
     * Cette liste doit correspondre à formatTableau() dans Point.php.
     */
    protected function getNomsColonnes(): array {
        return ["latitude", "longitude", "geom"];
    }


    /**
     * Méthode essentielle : transforme une ligne SQL en objet Voiture.
     *
     * Appelée automatiquement par :
     *  - selectAll()
     *  - select()
     *
     * $objetFormatTableau est un tableau associatif reçu depuis PDO::fetch().
     */
    public function construire(array $row): Point
    {
        return new Point(
            $row["id_point"],
            $row["latitude"],
            $row["longitude"],
            $row["geom"]
        );
    }


   public function selectLOD(int $step, int $limit): array
    {
        $pdo = DatabaseConnection::getPdo();

        $sql = "
            SELECT id_point, latitude, longitude
            FROM Points
            WHERE MOD(id_point, :step) = 0
            LIMIT :limit
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":step", $step, PDO::PARAM_INT);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }
}
