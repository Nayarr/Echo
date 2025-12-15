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
    <img src="/Echo/web/assets/img/Logo.png" alt="">
    <nav>
        <!-- Votre menu de navigation ici -->
        <a href="<?= $baseURL ?>?action=readAll" class="homebtn">Home</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=utilisateur" class="signin">Sign In</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=trajet" class="signup">Sign Up</a>
    </nav>
</header>
    <?php
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
<footer>
    
</footer>
</body>
</html>