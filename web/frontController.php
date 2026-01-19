<?php
// Fichier : web/frontController.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Lib/Psr4AutoloaderClass.php';

use Kreait\Firebase\Factory;
use App\SAE\Lib\Psr4AutoloaderClass;
use App\SAE\Lib\Session;

// 1. Chargement des classes
$loader = new Psr4AutoloaderClass();
$loader->addNamespace('App\SAE', __DIR__ . '/../src');
$loader->register();

// 2. Démarrage de la session
Session::start();

// =================================================================
// SÉCURITÉ : DÉCONNEXION AUTOMATIQUE (TIMEOUT)
// =================================================================

// Durée en secondes avant déconnexion (Mettez 10 pour tester, puis 1800 pour 30min)
$timeout_duration = 10; 

// Vérifier si on a une heure de dernière activité enregistrée
if (isset($_SESSION['LAST_ACTIVITY'])) {
    // Calculer le temps écoulé depuis la dernière action
    $duration = time() - $_SESSION['LAST_ACTIVITY'];
    
    // Si le temps écoulé est supérieur à la limite
    if ($duration > $timeout_duration) {
        // On détruit la session
        Session::destroy(); // Ou session_destroy();
        session_unset();    // Vide les variables
        
        // On recharge la page pour appliquer la déconnexion visuellement
        header("Location: frontController.php"); 
        exit();
    }
}

// Mettre à jour l'heure de dernière activité à MAINTENANT
$_SESSION['LAST_ACTIVITY'] = time();

// =================================================================
// FIN SÉCURITÉ
// =================================================================

// 3. Connexion Firebase
$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/../Cles/sae300-bf9d4-firebase-adminsdk-fbsvc-3f97406b36.json')
    ->withDatabaseUri('https://sae300-bf9d4-default-rtdb.europe-west1.firebasedatabase.app/');

// 4. Routage
$controller = $_REQUEST['controller'] ?? 'point';
$action     = $_REQUEST['action'] ?? 'carte';

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