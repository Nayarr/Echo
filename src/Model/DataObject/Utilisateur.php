<?php

namespace App\SAE\Model\DataObject;

class Utilisateur extends AbstractDataObject
{
    private int $id_utilisateur;
    private string $Prenom;
    private string $nom;
    private string $email;
    private string $mdp;
    private string $ProfilUtilisateur;

    public function __construct(
        int $id_utilisateur,
        string $Prenom,
        string $nom,
        string $email,
        string $mdp,
        string $ProfilUtilisateur
    ) {
        $this->id_utilisateur = $id_utilisateur;
        $this->Prenom = $Prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->ProfilUtilisateur = $ProfilUtilisateur;
    }

    public function getIdUtilisateur(): int
    {
        return $this->id_utilisateur;
    }

    public function getPrenom(): string
    {
        return $this->Prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    public function getProfilUtilisateur(): string
    {
        return $this->ProfilUtilisateur;
    }

    public function setPrenom(string $Prenom): void
    {
        $this->Prenom = $Prenom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setMdp(string $mdp): void
    {
        $this->mdp = $mdp;
    }

    public function setProfilUtilisateur(string $ProfilUtilisateur): void
    {
        $this->ProfilUtilisateur = $ProfilUtilisateur;
    }

    public function __toString(): string
    {
        return "Utilisateur #{$this->id_utilisateur} — {$this->Prenom} {$this->nom} ({$this->email}) [{$this->ProfilUtilisateur}]";
    }

    public function formatTableau(): array
    {
        return [
            "id_utilisateur" => $this->id_utilisateur,
            "Prenom" => $this->Prenom,
            "nom" => $this->nom,
            "email" => $this->email,
            "mdp" => $this->mdp,
            "ProfilUtilisateur" => $this->ProfilUtilisateur
        ];
    }
}
?>
