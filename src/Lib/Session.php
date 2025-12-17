<?php

namespace App\SAE\Lib;

use App\SAE\Model\DataObject\Utilisateur;

class Session
{
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setUser(Utilisateur $user): void {
        $_SESSION['utilisateur'] = [
            'id_utilisateur' => $user->getIdUtilisateur(),
            'Prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
            'email' => $user->getEmail(),
            'ProfilUtilisateur' => $user->getProfilUtilisateur()
        ];
    }

    public static function getUser(): ?array {
        return $_SESSION['utilisateur'] ?? null;
    }

    public static function isConnected(): bool {
        return isset($_SESSION['utilisateur']);
    }

    public static function destroy(): void {
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']);
            }
            session_destroy();
        }
    }
}
?>
