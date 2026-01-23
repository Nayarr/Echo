<?php

// Namespace correspondant au dossier "Model/Repository"
namespace App\SAE\Model\Repository;

use PDO;

// Import de la classe métier Utilisateur
use App\SAE\Model\DataObject\Utilisateur;

// Import de DatabaseConnection
use App\SAE\Model\Repository\DatabaseConnection as DatabaseConnection;

/**
 * Repository dédié à l'entité Utilisateur.
 *
 * Il hérite de AbstractRepository, ce qui lui donne :
 *  - create()
 *  - update()
 *  - delete()
 *  - select()
 *  - selectAll()
 *
 * Ici, on ajoute des méthodes spécifiques pour Utilisateur.
 */
class UtilisateurRepository extends AbstractRepository
{
    /**
     * Indique le nom exact de la table SQL associée.
     */
    protected function getNomTable(): string {
        return "utilisateur";
    }

    /**
     * Indique le nom de la clé primaire de la table.
     */
    protected function getNomClePrimaire(): string {
        return "id_utilisateur";
    }

    /**
     * Retourne la liste des colonnes SQL utilisées pour INSERT/UPDATE.
     * Cette liste doit correspondre à formatTableau() dans Utilisateur.php.
     */
    protected function getNomsColonnes(): array {
        return ["Prenom", "nom", "email", "mdp", "ProfilUtilisateur"];
    }

    /**
     * Transforme une ligne SQL en objet Utilisateur.
     */
    public function construire(array $row): Utilisateur {
        return new Utilisateur(
            $row["id_utilisateur"],
            $row["Prenom"],
            $row["nom"],
            $row["email"],
            $row["mdp"],
            $row["ProfilUtilisateur"]
        );
    }

    /**
     * Cherche un utilisateur par email.
     */
    public function findByEmail(string $email): ?Utilisateur {
        $pdo = DatabaseConnection::getPdo();
        $sql = "SELECT * FROM " . $this->getNomTable() . " WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["email" => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row === false) {
            return null;
        }
        return $this->construire($row);
    }

    /**
     * Vérifie le mot de passe d'un utilisateur.
     * Retourne l'utilisateur si le mot de passe est correct, null sinon.
     */
    public function verifyPassword(string $email, string $password): ?Utilisateur {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user->getMdp())) {
            return $user;
        }
        return null;
    }
}
?>
