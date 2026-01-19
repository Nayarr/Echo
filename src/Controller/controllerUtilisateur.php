<?php
// Fichier : src/Controller/controllerUtilisateur.php

namespace App\SAE\Controller;

use App\SAE\Config\Conf;
use App\SAE\Lib\Session;
use App\SAE\Model\DataObject\Utilisateur;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class controllerUtilisateur
{
    private $auth;
    private $database;

    // Constructeur obligatoire pour recevoir la connexion Firebase
    public function __construct($factory) {
        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    // --- INSCRIPTION ---

    public function inscription(): void {
        self::afficheVue('utilisateur/view.php', [
            "pagetitle" => "Inscription",
            "cheminVueBody" => "inscription.php"
        ]);
    }

    public function created(): void {
        $prenom = $_POST['Prenom'] ?? '';
        $nom    = $_POST['nom'] ?? '';
        $email  = $_POST['email'] ?? '';
        $mdp    = $_POST['mdp'] ?? '';
        $cmdp   = $_POST['Cmdp'] ?? '';
        $profil = $_POST['ProfilUtilisateur'] ?? 'GrandPublic';

        if ($mdp !== $cmdp) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Les mots de passe ne correspondent pas."
            ]);
            return;
        }

        try {
            // 1. Créer le compte Auth (Email/Pass)
            $userProperties = [
                'email' => $email,
                'emailVerified' => false,
                'password' => $mdp,
                'displayName' => "$prenom $nom",
                'disabled' => false,
            ];
            $createdUser = $this->auth->createUser($userProperties);

            // 2. Stocker les infos dans la Database
            $this->database->getReference('users/' . $createdUser->uid)->set([
                'prenom' => $prenom,
                'nom' => $nom,
                'email' => $email,
                'role' => $profil,
                'date_creation' => time()
            ]);

            // 3. Connecter l'utilisateur (Session PHP)
            Session::start();
            // On stocke l'UID Firebase en session
            $_SESSION['user_uid'] = $createdUser->uid;
            $_SESSION['user_prenom'] = $prenom;

            header('Location: frontController.php?controller=point&action=carte');
            exit();

        } catch (EmailExists $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Email déjà utilisé."
            ]);
        } catch (\Exception $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Erreur",
                "cheminVueBody" => "inscription.php",
                "error" => "Erreur : " . $e->getMessage()
            ]);
        }
    }

    // --- CONNEXION ---

    public function connexion(): void {
        self::afficheVue('utilisateur/view.php', [
            "pagetitle" => "Connexion",
            "cheminVueBody" => "conection.php"
        ]);
    }

    public function traiterConnexion(): void {
        $email = $_POST['email'] ?? '';
        $mdp   = $_POST['mdp'] ?? '';

        try {
            // 1. Vérifier Email/Mdp auprès de Firebase
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $mdp);
            
            // 2. Récupérer l'info utilisateur
            $uid = $signInResult->firebaseUserId();
            
            // 3. Récupérer les infos supplémentaires depuis la Database (Prénom, Nom)
            $snapshot = $this->database->getReference('users/' . $uid)->getSnapshot();
            $userData = $snapshot->getValue();
            $prenom = $userData['prenom'] ?? 'Utilisateur';

            // 4. Mettre en Session PHP
            Session::start();
            $_SESSION['user_uid'] = $uid;
            $_SESSION['user_prenom'] = $prenom;

            header('Location: frontController.php?controller=point&action=carte');
            exit();

        } catch (\Exception $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Connexion",
                "cheminVueBody" => "conection.php",
                "error" => "Email ou mot de passe incorrect."
            ]);
        }
    }

    public function logout(): void {
        Session::destroy();
        header('Location: frontController.php?controller=point&action=carte');
        exit();
    }
}
?>