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
        <div class="nav-brand">
            <a href="<?= $baseURL ?>?action=accueil" class="homebtn">
                <img src="/Echo/web/assets/img/LogoEcho2.png" alt="Logo Echo">
            </a>
        </div>

        <nav class="nav-links">
            <a href="frontController.php?controller=point&action=carte">Carte</a>
            <a href="frontController.php?controller=point&action=source">Source</a> 
            <a href="frontController.php?controller=point&action=equipe">Equipe</a>    
        </nav>

        <div class="nav-actions">
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
    <div class="bg-section">
    <?php
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
    <footer class="footer">
        <div class="footer-inner">

            <!-- Gauche -->
            <div class="footer-left">
            <img src="/Echo/web/assets/img/LogoEcho2.png" alt="Echo Logo" class="footer-logo">

            <div class="footer-socials">
                <img src="/Echo/web/assets/img/insta.svg" alt="Instagram">
                <img src="/Echo/web/assets/img/x.svg" alt="X">
                <img src="/Echo/web/assets/img/lnkd.svg" alt="LinkedIn">
                <img src="/Echo/web/assets/img/github.svg" alt="GitHub">
            </div>
            </div>

            <!-- Droite -->
            <div class="footer-right">
            <h5>Entreprise</h5>
            <a href="#">À propos</a>
            <a href="#">Team</a>
            <a href="#">Contact</a>
            <a href="#">Support</a>
            </div>

        </div>

        <div class="footer-bottom">
            © 2025 Echo. All rights reserved.
        </div>
    </footer>
</div>
</body>
</html>
