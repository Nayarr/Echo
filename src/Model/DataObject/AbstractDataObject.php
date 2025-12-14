<?php

// Namespace du dossier contenant les objets métiers (Voiture, Utilisateur, Trajet…)
namespace App\SAE\Model\DataObject;

abstract class AbstractDataObject
{
    /**
     * Méthode abstraite formatTableau()
     *
     * Chaque classe fille DOIT l’implémenter.
     * Ce mécanisme :
     * - oblige les objets métiers à fournir les données nécessaires aux requêtes SQL
     * - garantit une interface commune pour les repositories
     *
     * Le tableau retourné contient :
     *   clé   → nom de la colonne SQL
     *   valeur → valeur à insérer / mettre à jour
     *
     * Exemple dans Trajet :
     *   ["depart" => "Paris", "prix" => 12, ...]
     *
     * C’est grâce à cette méthode que AbstractRepository peut fonctionner
     * de manière générique avec n'importe quel objet métier.
     */
    public abstract function formatTableau(): array;
}
