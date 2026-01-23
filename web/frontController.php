<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Lib/Psr4AutoloaderClass.php';

use Kreait\Firebase\Factory;
use App\SAE\Lib\Psr4AutoloaderClass;
use App\SAE\Lib\Session;

// ============================================================
// 1. CONFIGURATION DU TEMPS DE SESSION
// ============================================================

$duree_session = 1800; 

// ============================================================
// 2. CHARGEMENT AUTOMATIQUE
// ============================================================
$loader = new Psr4AutoloaderClass();
$loader->addNamespace('App\SAE', __DIR__ . '/../src');
$loader->register();

// ============================================================
// 3. GESTION DE LA SESSION ET DU TIMEOUT
// ============================================================
// On configure le "Garbage Collector" de PHP pour qu'il corresponde à votre durée
ini_set('session.gc_maxlifetime', $duree_session);
session_set_cookie_params($duree_session);

Session::start();

// Vérification d'inactivité
if (isset($_SESSION['derniere_activite']) && (time() - $_SESSION['derniere_activite'] > $duree_session)) {
    // Si le temps est dépassé : On déconnecte
    Session::destroy();
    session_unset();
    
    // On redirige vers l'accueil
    header("Location: frontController.php?controller=point&action=carte");
    exit();
}

// On met à jour l'heure de dernière activité à MAINTENANT
$_SESSION['derniere_activite'] = time();

// ============================================================
// 4. CONNEXION FIREBASE
// ============================================================
$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/../Cles/sae300-bf9d4-firebase-adminsdk-fbsvc-3f97406b36.json')
    ->withDatabaseUri('https://sae300-bf9d4-default-rtdb.europe-west1.firebasedatabase.app/');

// ============================================================
// 5. ROUTAGE 
// ============================================================
$controller = $_REQUEST['controller'] ?? 'point';
$action     = $_REQUEST['action'] ?? 'accueil';

$controllerClassName = "App\\SAE\\Controller\\controller" . ucfirst($controller);

if (class_exists($controllerClassName)) {
    $controllerInstance = new $controllerClassName($factory);
    
    if (in_array($action, get_class_methods($controllerClassName))) {
        $controllerInstance->$action();
    } else {
        header('Location: frontController.php?controller=point&action=carte');
        exit();
    }
} else {
    echo "Erreur : Contrôleur '$controllerClassName' introuvable.";
}
?>