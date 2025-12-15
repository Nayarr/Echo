<?php 

namespace App\SAE\Controller;
use App\Model\DataObject\Utilisateur;

class ControllerUtilisateur {

    // Affiche le formulaire de connexion avec Firebase
    public static function afficherFormulaireConnexion() {
        // Le script JS de Firebase sera inclus dans la vue
        require (__DIR__ . '/../view/view.php'); 
    }

    // Traite la connexion une fois que le token envoyé
    public function connecter() {
        // Récupérer le token envoyé par le formulaire (POST)
        // Vérifier le token
        // Si valide, créer une session utilisateur
        session_start();
        $_SESSION['login'] = $_POST['email']; // ou l'UID
        
        // Redirection vers l'accueil
        header("Location: frontController.php");
    }

    public function deconnecter() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: frontController.php");
    }
}

?>