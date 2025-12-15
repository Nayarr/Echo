<?php

/*
PSR-4 autoloader example :
Ce fichier est basé sur l'implémentation officielle recommandée par le PHP-FIG.
Il sert à charger automatiquement les classes du projet en fonction de leur namespace.
*/

namespace App\SAE\Lib;

class Psr4AutoloaderClass
{
    /**
     * Tableau associatif contenant :
     * - clé   = préfixe de namespace (ex : "App\Covoiturage\")
     * - valeur = tableau de répertoires dans lesquels chercher les classes
     *
     * Exemple :
     * $prefixes["App\Covoiturage\"] = ["/var/www/html/src/"]
     */
    protected array $prefixes = array();


    /**
     * Enregistre cette classe comme autoloader auprès de PHP.
     * spl_autoload_register demande à PHP d'appeler loadClass()
     * à chaque fois qu'une classe non déclarée est utilisée.
     */
    public function register() : void
    {
        spl_autoload_register(array($this, 'loadClass'));
    }


    /**
     * Ajoute un espace de nom (namespace) et le répertoire associé.
     *
     * Exemple :
     * addNamespace("App\Covoiturage", "/var/www/src")
     *
     * @param string $prefix     Le namespace des classes
     * @param string $base_dir   Le dossier physique où elles se trouvent
     * @param bool $prepend      Si true : met le dossier en priorité
     */
    public function addNamespace(string $prefix, string $base_dir, bool $prepend = false) : void
    {
        // Normalise le namespace : retire les "\" inutiles, ajoute un "\" final
        $prefix = trim($prefix, '\\') . '\\';

        // Normalise le répertoire : supprime "/" finaux, ajoute le "/"
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        // Si ce prefix n'existe pas encore, on l'initialise comme tableau vide
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }

        // Ajoute ce répertoire au tableau des répertoires du namespace
        // prepend = priorité supérieure
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }


    /**
     * Tente de charger la classe demandée automatiquement.
     * @param string $class Nom complet de la classe (ex : App\Covoiturage\Model\Trajet)
     */
    public function loadClass(string $class)
    {
        // On commence par prendre le namespace complet
        $prefix = $class;

        // On remonte dans la hiérarchie du namespace pour essayer plusieurs correspondances
        while (false !== $pos = strrpos($prefix, '\\')) {

            // Préfixe : tout avant le dernier "\"
            $prefix = substr($class, 0, $pos + 1);

            // Classe relative : ce qu'il y a après le dernier "\"
            $relative_class = substr($class, $pos + 1);

            // On essaie de charger un fichier correspondant à ce namespace
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                // Si un fichier valide est trouvé → on le charge et on arrête
                return $mapped_file;
            }

            // Sinon : on retire le "\" final et on réessaie au niveau supérieur
            $prefix = rtrim($prefix, '\\');
        }

        // Aucun fichier trouvé → autoload échoue silencieusement
        return false;
    }


    /**
     * Charge un fichier correspondant à un namespace précis.
     *
     * Exemple :
     * prefix = "App\Covoiturage\"
     * relative_class = "Model/Trajet"
     * file = "/src/Model/Trajet.php"
     */
    protected function loadMappedFile(string $prefix, string $relative_class)
    {
        // Si aucun répertoire n'est mappé à ce namespace, inutile d'aller plus loin
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // On vérifie dans chaque répertoire lié à ce namespace
        foreach ($this->prefixes[$prefix] as $base_dir) {

            // Construit le chemin complet du fichier attendu :
            // - remplace "\" du namespace par "/"
            // - ajoute ".php"
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            // Si le fichier existe, on le charge via require
            if ($this->requireFile($file)) {
                return $file;
            }
        }

        // Aucun fichier trouvé
        return false;
    }


    /**
     * Vérifie l’existence du fichier et le charge.
     *
     * @return bool True si le fichier existe et a été chargé
     */
    protected function requireFile(string $file) : bool
    {
        if (file_exists($file)) {
            require $file;  // le fichier PHP est inclus et exécuté
            return true;
        }
        return false;
    }
}
