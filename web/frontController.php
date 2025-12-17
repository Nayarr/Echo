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

// Démarrage centralisé des sessions via le helper `Session`.
// Appelé ici (après l'enregistrement de l'autoload) pour s'assurer
// que la classe `App\SAE\Lib\Session` peut être résolue.
// `Session::start()` vérifie `session_status()` avant d'appeler `session_start()`.
\App\SAE\Lib\Session::start();




// Récupération des paramètres depuis la requête (GET ou POST)
$action = $_REQUEST['action'] ?? 'carte';
$controller = $_REQUEST['controller'] ?? 'point';

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

