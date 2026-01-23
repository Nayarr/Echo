<?php
namespace App\SAE\Controller;

use App\SAE\Config\Conf;
use App\SAE\Lib\Session;
// Import du Repository pour gérer les favoris (MySQL)
use App\SAE\Model\Repository\PointRepository; 

// Imports pour gérer les erreurs Firebase proprement
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\WeakPassword;

class controllerUtilisateur
{
    private $auth;
    private $database;

    // Le constructeur reçoit la Factory Firebase depuis le FrontController
    public function __construct($factory) {
        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    // Fonction utilitaire pour afficher les vues
    private static function afficheVue(string $cheminVue, array $parametres = []): void {
        $parametres['baseURL'] = Conf::getBaseURL();
        extract($parametres);
        require __DIR__ . "/../view/$cheminVue";
    }

    // ============================================================
    // 1. INSCRIPTION
    // ============================================================

    public function inscription(): void {
        self::afficheVue('utilisateur/view.php', [
            "pagetitle" => "Inscription",
            "cheminVueBody" => "inscription.php"
        ]);
    }

    public function created(): void {
        // Protection si formulaire vide
        if (empty($_POST)) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Erreur : Le formulaire est vide."
            ]);
            return;
        }

        $prenom = $_POST['Prenom'] ?? '';
        $nom    = $_POST['nom'] ?? '';
        $email  = $_POST['email'] ?? '';
        $mdp    = $_POST['mdp'] ?? '';
        $cmdp   = $_POST['Cmdp'] ?? '';
        $profil = $_POST['ProfilUtilisateur'] ?? 'GrandPublic';

        // Vérification Mots de passe
        if ($mdp !== $cmdp) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Les mots de passe ne correspondent pas."
            ]);
            return;
        }

        if (strlen($mdp) < 6) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Le mot de passe est trop court (min 6 caractères)."
            ]);
            return;
        }

        try {
            // Création Firebase Auth
            $userProperties = [
                'email' => $email,
                'emailVerified' => false,
                'password' => $mdp,
                'displayName' => "$prenom $nom",
                'disabled' => false,
            ];

            $createdUser = $this->auth->createUser($userProperties);

            // Enregistrement dans la Database
            $this->database->getReference('users/' . $createdUser->uid)->set([
                'prenom' => $prenom,
                'nom' => $nom,
                'email' => $email,
                'role' => $profil,
                'date_creation' => time()
            ]);

            // Connexion automatique (Session)
            if (session_status() === PHP_SESSION_NONE) {
                Session::start();
            }
            $_SESSION['user_uid'] = $createdUser->uid;
            $_SESSION['user_prenom'] = $prenom;

            // Redirection vers la carte
            header('Location: frontController.php?controller=point&action=carte');
            exit();

        } catch (EmailExists $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Cet email est déjà utilisé par un autre compte."
            ]);
        } catch (WeakPassword $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Mot de passe trop faible."
            ]);
        } catch (\Exception $e) {
            self::afficheVue('utilisateur/view.php', [
                "pagetitle" => "Inscription",
                "cheminVueBody" => "inscription.php",
                "error" => "Erreur technique : " . $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // 2. CONNEXION
    // ============================================================

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
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $mdp);
            $uid = $signInResult->firebaseUserId();
            
            $snapshot = $this->database->getReference('users/' . $uid)->getSnapshot();
            $val = $snapshot->getValue();
            $prenom = $val['prenom'] ?? 'Utilisateur';

            if (session_status() === PHP_SESSION_NONE) Session::start();
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

    // ============================================================
    // 3. DÉCONNEXION
    // ============================================================

    public function logout(): void {
        Session::destroy();
        header('Location: frontController.php?controller=point&action=carte');
        exit();
    }

    // ============================================================
    // 4. FAVORIS
    // ============================================================

    public function mesFavoris(): void {
        if (!isset($_SESSION['user_uid'])) {
            header('Location: frontController.php?controller=utilisateur&action=connexion');
            exit();
        }

        $favorisDetails = [];

        try {
            $uid = $_SESSION['user_uid'];
            $snapshot = $this->database->getReference("users/$uid/favoris")->getSnapshot();

            if ($snapshot->exists()) {
                $idsFirebase = array_keys($snapshot->getValue());
                $pointRepo = new PointRepository();
                $favorisDetails = $pointRepo->selectManyById($idsFirebase);
            }
        } catch (\Exception $e) {
            // Liste vide en cas d'erreur
        }

        self::afficheVue('utilisateur/view.php', [
            "pagetitle" => "Mes Favoris",
            "cheminVueBody" => "favoris.php",
            "points" => $favorisDetails
        ]);
    }

    public function toggleFavori(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_uid'])) {
            echo json_encode(['status' => 'error', 'message' => 'Non connecté']);
            return;
        }

        $uid = $_SESSION['user_uid'];
        $pointId = $_GET['id_point'] ?? null;

        if (!$pointId) {
            echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
            return;
        }

        try {
            $ref = $this->database->getReference("users/$uid/favoris/$pointId");
            $snap = $ref->getSnapshot();

            if ($snap->exists()) {
                $ref->remove();
                echo json_encode(['status' => 'success', 'action' => 'removed']);
            } else {
                $ref->set(time());
                echo json_encode(['status' => 'success', 'action' => 'added']);
            }
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();
    }
}
?>