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
        <a href="<?= $baseURL ?>?action=accueil" class="homebtn">Home</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=utilisateur" class="signin">Sign In</a>
        <a href="<?= $baseURL ?>?action=readAll&controller=trajet" class="signup">Sign Up</a>
    </nav>
</header>
    <?php
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
<footer>
    <div class="conteneur">
        <div class="footer_conteneur">
            <div class="footer_haut">
                <div class="footer_gauche">
                    <img src="/Echo/web/assets/img/logo.png" alt="">
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eget est a tellus feugiat varius. Phasellus porttitor odio et quam semper consectetur. 
                    </p>
                    <div class="footer_logo">
                        <img src="/Echo/web/assets/img/insta.svg" alt="">
                        <img src="/Echo/web/assets/img/x.svg" alt="">
                        <img src="/Echo/web/assets/img/lnkd.svg" alt="">
                        <img src="/Echo/web/assets/img/github.svg" alt="">
                    </div>
                </div>
                <div class="footer_droit">
                    <div class="footer_column">
                        <h5>Entreprise</h5>
                        <a href="#">À propos</a>
                        <a href="#">Team</a>
                        <a href="#">Contact</a>
                        <a href="#">Support</a>
                    </div>

                    <div class="footer_column">
                        <h5>Entreprise</h5>
                        <a href="#">À propos</a>
                        <a href="#">Team</a>
                        <a href="#">Contact</a>
                        <a href="#">Support</a>
                    </div>

                    <div class="footer_column">
                        <h5>Entreprise</h5>
                        <a href="#">À propos</a>
                        <a href="#">Team</a>
                        <a href="#">Contact</a>
                        <a href="#">Support</a>
                    </div>
                </div>
            </div>
            
            <p>© 2025 Echo. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>