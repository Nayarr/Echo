<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <link rel="stylesheet" href="../web/assets/css/style.css">
    
    <?php if ($pagetitle === "Carte des points"): ?>
        <link href="https://unpkg.com/maplibre-gl@3.6.0/dist/maplibre-gl.css" rel="stylesheet"/>
    <?php endif; ?>
</head>
<body>

<header>
    <div class="nav-brand">
        <div class="nav-brand-icon">E</div> <span>Echo</span>
    </div>

    <nav class="nav-links">
        <a href="frontController.php?controller=point&action=carte">Carte</a>
        <a href="#">Roadmap</a> <a href="#">Docs</a>    </nav>

    <div class="nav-actions">
        <div class="search-bar">
            <span>🔍 Rechercher...</span>
        </div>

        <?php if (isset($_SESSION['user_uid'])): ?>
            <a href="#" class="btn-nav btn-secondary">
                👤 <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Moi') ?>
            </a>
            <a href="frontController.php?controller=utilisateur&action=logout" class="btn-nav btn-primary">
                Déco
            </a>
        <?php else: ?>
            <a href="frontController.php?controller=utilisateur&action=connexion" class="btn-nav btn-secondary">
                Log in
            </a>
            <a href="frontController.php?controller=utilisateur&action=inscription" class="btn-nav btn-primary">
                Sign up
            </a>
        <?php endif; ?>
    </div>
</header>

<main <?php echo ($pagetitle === "Carte des points") ? 'class="carte-view"' : ''; ?>>
    <?php require __DIR__ . "/{$cheminVueBody}"; ?>
</main>

<footer>
    <p style="text-align:center; margin-top:30px; color:#888; font-size:0.8em;">Projet SAE 300 - Echo Maritime</p>
</footer>

</body>
</html>