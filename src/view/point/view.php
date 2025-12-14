<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link rel="stylesheet" href="/TD6/web/assets/css/style.css">
</head>
<body>
<header>
    <nav>
        <!-- Votre menu de navigation ici -->
        <a href="<?= $baseURL ?>?action=readAll">Voiture</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=utilisateur">Utilisateur</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=trajet">Trajets</a>
    </nav>
</header>
<main>

    <?php
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
<footer>
    <p>Site de covoiturage de RAYAN</p>
</footer>
</body>
</html>

