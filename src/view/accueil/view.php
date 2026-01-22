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
    <a href="<?= $baseURL ?>?action=accueil" class="homebtn">
    <img src="/Echo/web/assets/img/LogoEcho2.png" alt="Logo Echo">
    </a>
    
    <nav>
        <!-- Votre menu de navigation ici -->
        <a
            href="<?= $baseURL ?>?action=readAll&controller=trajet"
            aria-label="User Login Button"
            tabindex="0"
            role="button"
            class="user-profile"
            >
            <div class="user-profile-inner">
                <svg
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                >
                <g data-name="Layer 2" id="Layer_2">
                    <path
                    d="m15.626 11.769a6 6 0 1 0 -7.252 0 9.008 9.008 0 0 0 -5.374 8.231 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 9.008 9.008 0 0 0 -5.374-8.231zm-7.626-4.769a4 4 0 1 1 4 4 4 4 0 0 1 -4-4zm10 14h-12a1 1 0 0 1 -1-1 7 7 0 0 1 14 0 1 1 0 0 1 -1 1z"
                    ></path>
                </g>
                </svg>
                <p>Connexion</p>
            </div>
        </a>

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