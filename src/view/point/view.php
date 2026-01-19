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
    <nav style="display: flex; gap: 15px; align-items: center; justify-content: center; padding: 10px;">
        
        <a href="frontController.php?controller=point&action=carte" style="font-weight: bold; color: #333; text-decoration: none;">📍 Carte</a>

        <?php if (isset($_SESSION['user_uid'])): ?>
            <span style="color: green; font-weight: bold;">👤 Bonjour <?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?></span>
            <a href="frontController.php?controller=utilisateur&action=logout" style="color: red; text-decoration: none;">Déconnexion</a>
        <?php else: ?>
            <a href="frontController.php?controller=utilisateur&action=inscription" style="padding: 5px 10px; background: #007bff; color: white; border-radius: 4px; text-decoration: none;">Inscription</a>
            <a href="frontController.php?controller=utilisateur&action=connexion" style="color: #007bff; text-decoration: none;">Connexion</a>
        <?php endif; ?>

    </nav>
</header>

<main <?php echo ($pagetitle === "Carte des points") ? 'class="carte-view"' : ''; ?>>
    <?php require __DIR__ . "/{$cheminVueBody}"; ?>
</main>

<footer><p style="text-align:center; font-size:0.8em; margin-top:20px;">SAE 300</p></footer>
</body>
</html>