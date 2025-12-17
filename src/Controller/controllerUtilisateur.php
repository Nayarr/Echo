<?php

namespace App\SAE\Controller;

use App\SAE\Model\Repository\UtilisateurRepository;
use App\SAE\Model\DataObject\Utilisateur;
use App\SAE\Config\Conf;
use App\SAE\Lib\Session;
use App\SAE\Model\Repository\DatabaseConnection as DatabaseConnection;

class controllerUtilisateur
{
    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    // Affiche le formulaire d'inscription
    public static function inscription(): void {
        controllerUtilisateur::afficheVue('point/view.php', [
            "pagetitle" => "Inscription",
            "cheminVueBody" => "inscription.php"
        ]);
    }

    // Traite la soumission du formulaire d'inscription
    public static function created(): void {
        // Récupération des données POST
        $Prenom = $_POST['Prenom'] ?? null;
        $nom = $_POST['nom'] ?? null;
        $email = $_POST['email'] ?? null;
        $mdp = $_POST['mdp'] ?? null;
        $Cmdp = $_POST['Cmdp'] ?? null;
        $ProfilUtilisateur = $_POST['ProfilUtilisateur'] ?? null;

        if (!$Prenom || !$nom || !$email || !$mdp || !$Cmdp) {
            // données manquantes → réafficher formulaire avec message d'erreur
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Veuillez remplir tous les champs du formulaire."
            ]);
            return;
        }

        if ($mdp !== $Cmdp) {
            // mot de passe différent → réafficher formulaire avec message d'erreur
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Les mots de passe ne correspondent pas."
            ]);
            return;
        }

        // Hash du mot de passe
        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        // Création de l'objet utilisateur (id = 0 temporaire)
        $user = new Utilisateur(0, $Prenom, $nom, $email, $hash, $ProfilUtilisateur ?? 'GrandPublic');

        $repo = new UtilisateurRepository();

        // Vérifier si l'email est déjà utilisé
        $existing = $repo->findByEmail($email);
        if ($existing !== null) {
            // Email déjà présent → réafficher le formulaire avec message d'erreur
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Cette adresse email est déjà utilisée. Connectez-vous ou choisissez une autre adresse."
            ]);
            return;
        }

        // Tenter l'insertion en base avec gestion d'exception
        try {
            $repo->create($user);
        } catch (\Exception $e) {
            // En cas d'erreur SQL (ex : contrainte), afficher un message
            // ATTENTION: affichage temporaire pour debug — retirer en production
            $msg = "Erreur lors de l'enregistrement de votre compte. Veuillez réessayer plus tard.";
            $msgDetail = $e->getMessage();
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => $msg . ' (' . $msgDetail . ')'
            ]);
            return;
        }

        // Recharger l'utilisateur depuis la BDD pour obtenir l'id et les données exactes
        $created = $repo->findByEmail($email);

        // Démarrer la session via le helper
        Session::start();
        // Régénérer l'ID de session après inscription pour prévenir la fixation de session
        // (sécurité : empêche un attaquant de réutiliser un ancien ID de session)
        session_regenerate_id(true);

        if ($created) {
            // Stocke uniquement les informations non sensibles en session
            Session::setUser($created);
        }

        // Redirection vers la carte (ou autre vue post-inscription)
        header('Location: ' . Conf::getBaseURL() . '/web/frontController.php?action=carte&controller=point');
        exit();
    }

    // Affiche la page de connexion
    public static function connexion(): void {
        controllerUtilisateur::afficheVue('point/view.php', [
            "pagetitle" => "Connexion",
            "cheminVueBody" => "conection.php"
        ]);
    }

    // Traite la soumission du formulaire de connexion
    public static function traiterConnexion(): void {
        $email = $_POST['email'] ?? null;
        $mdp = $_POST['mdp'] ?? null;

        if (!$email || !$mdp) {
            // données manquantes → réafficher formulaire de connexion avec message
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Connexion",
                "cheminVueBody" => "conection.php",
                "error" => "Veuillez saisir votre email et votre mot de passe."
            ]);
            return;
        }

        $repo = new UtilisateurRepository();
        $user = $repo->verifyPassword($email, $mdp);

        if ($user) {
            // Démarrer la session puis régénérer l'ID de session
            // pour empêcher la fixation de session après l'authentification.
            Session::start();
            session_regenerate_id(true);

            Session::setUser($user);

            header('Location: ' . Conf::getBaseURL() . '/web/frontController.php?action=carte&controller=point');
            exit();
        } else {
            // Échec de l'authentification → réafficher le formulaire avec message
            controllerUtilisateur::afficheVue('point/view.php', [
                "pagetitle" => "Connexion - échec",
                "cheminVueBody" => "conection.php",
                "error" => "Email ou mot de passe incorrect."
            ]);
        }
    }

    // Déconnexion : détruit la session
    public static function logout(): void {
        Session::start();
        Session::destroy();
        header('Location: ' . Conf::getBaseURL() . '/web/frontController.php?action=carte&controller=point');
        exit();
    }
}

?>
