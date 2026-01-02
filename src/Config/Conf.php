<?php

// Déclare le namespace de ce fichier.
// Cela organise le code et permet à l'autoloader de retrouver la classe.
namespace App\SAE\Config;

// Cette classe sert uniquement à stocker des informations de configuration.
// On y trouve : l'URL de base du site et les paramètres de connexion à MySQL.
class Conf
{
    // Déclare une variable statique contenant l'URL du frontController.
    // Le frontController est le point d'entrée unique du site (architecture MVC).
    // Cette URL est utilisée dans les vues pour générer des liens propres.
    static private string $baseURL = "/Echo/web/frontController.php";

    // Méthode publique statique permettant d'accéder à l'URL du frontController.
    // Le mot-clé static signifie que l'on n'a pas besoin d'instancier Conf pour l'appeler.
    public static function getBaseURL(): string {
        return self::$baseURL;   // self:: permet d'accéder aux attributs statiques.
    }


    // Tableau contenant les informations de connexion à la base MySQL.
    // Tout est regroupé ici pour éviter de disperser la configuration dans plusieurs fichiers.
    static private array $databases = array(
        // Adresse du serveur MySQL (souvent localhost en local)
        'hostname' => 'localhost',

        // Nom de la base de données à laquelle on veut se connecter
        'database' => 'Echo',

        // Identifiant MySQL (root par défaut sur XAMPP/MAMP)
        'login' => 'root',

        // Mot de passe correspondant au login (souvent vide en local)
        'password' => 'root'
    );


    // Retourne le login MySQL pour la connexion.
    // static:: permet d'accéder à un attribut statique depuis une méthode statique.
    static public function getLogin(): string
    {
        return static::$databases['login'];
    }

    // Retourne le nom d'hôte (serveur MySQL)
    static public function getHostname(): string
    {
        return static::$databases['hostname'];
    }

    // Retourne le nom de la base de données à utiliser
    static public function getDatabase(): string
    {
        return static::$databases['database'];
    }

    // Retourne le mot de passe MySQL
    static public function getPassword(): string
    {
        // Si le serveur exécute Windows, les environnements locaux (XAMPP/MAMP)
        // utilisent souvent un mot de passe vide pour l'utilisateur root.
        // On détecte l'OS ici et on retourne une chaîne vide sous Windows,
        // sinon on retourne le mot de passe configuré.
        if (defined('PHP_OS_FAMILY')) {
            if (PHP_OS_FAMILY === 'Windows') {
                return '';
            }
        } else {
            // Compatibilité pour anciennes versions de PHP
            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                return '';
            }
        }

        return static::$databases['password'];
    }
}

?>
