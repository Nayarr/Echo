<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Inclusion de la classe d’autoload pour charger automatiquement les classes du projet
require_once __DIR__ . '/../src/Lib/Psr4AutoloaderClass.php';

// Import du contrôleur principal des voitures
use App\SAE\Controller\ControllerPoint;

// Instanciation et configuration du chargeur automatique (autoload)
$loader = new App\SAE\Lib\Psr4AutoloaderClass();
$loader->addNamespace('App\SAE', __DIR__ . '/../src');
$loader->register();

// Récupération des paramètres depuis l’URL
$action = $_GET['action'] ?? 'carte';
$controller = $_GET['controller'] ?? 'point';

// Si c'est une requête API, désactiver l'affichage d'erreurs HTML
// pour éviter d'envoyer des pages d'erreur PHP aux clients qui attendent du JSON.
if (is_string($action) && strpos($action, 'api') === 0) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// si ca renvoie ca : frontController.php?action=readAll&controller=trajet

// Construction dynamique du nom de classe du contrôleur
$controllerClassName = "App\\SAE\\Controller\\controller" . ucfirst($controller);

// Vérification de l’existence du contrôleur et de l’action
if (class_exists($controllerClassName)) {
    if (in_array($action, get_class_methods($controllerClassName))) {
        $controller = new $controllerClassName();
        $controller->$action();
    }
}

