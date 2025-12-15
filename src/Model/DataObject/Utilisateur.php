<?php

namespace App\SAE\Model\DataObject;
use App\Model\DataObject\AbstractDataObject;



class Utilisateur extends AbstractDataObject {
    
    private string $email;
    private string $uid; // L'identifiant Firebase

    public function __construct(string $email, string $uid) {
        $this->email = $email;
        $this->uid = $uid;
    }

    public function getEmail(): string { 
        return $this->email; 
    }

    public function getUid(): string { 
        return $this->uid; 
    }

    // permet de formater l'objet en tableau associatif
    public function formatTableau(): array {
        return [
            "emailTag" => $this->email,
            "uidTag" => $this->uid
        ];
    }
}

?>