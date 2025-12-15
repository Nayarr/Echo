<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link rel="stylesheet" href="/Echo/web/assets/css/style.css">
    <?php if ($pagetitle === "Carte des points"): ?>
    <link href="https://unpkg.com/maplibre-gl@3.6.0/dist/maplibre-gl.css" rel="stylesheet"/>
    <?php endif; ?>
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
<main <?php echo ($pagetitle === "Carte des points") ? 'class="carte-view"' : ''; ?>>

    <?php
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
<footer>
    <p>Echo </p>
</footer>
</body>
</html>

