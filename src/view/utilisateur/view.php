<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link rel="stylesheet" href="../web/assets/css/style.css">
</head>
<body>
<header>
    <nav style="display: flex; gap: 15px; align-items: center; justify-content: center; padding: 10px;">
        
        <a href="frontController.php?controller=point&action=carte" style="text-decoration: none; color: #333; font-weight: bold;">
            📍 Carte
        </a>

        <a href="frontController.php?controller=utilisateur&action=inscription" 
           style="padding: 8px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
            S'inscrire
        </a>

        <a href="frontController.php?controller=utilisateur&action=connexion" style="text-decoration: none; color: #007bff;">
            Se connecter
        </a>

    </nav>
</header>

<main>
    <?php
    // Inclut le fichier spécifique (inscription.php ou conection.php)
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>

<footer>
    <p style="text-align:center; margin-top:20px;">SAE 300 - Utilisateurs</p>
</footer>
</body>
</html>