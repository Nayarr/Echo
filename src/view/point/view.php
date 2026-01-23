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
                <span>Connexion</span>
            </div>
        </a>

    </nav>
</header>
<main <?php echo ($pagetitle === "Carte des points") ? 'class="carte-view"' : ''; ?>>

<header>
    <div class="nav-brand">
        <a href="<?= $baseURL ?>?action=accueil" class="homebtn">
            <img src="/Echo/web/assets/img/LogoEcho2.png" alt="Logo Echo">
        </a>
    </div>

    <nav class="nav-links">
        <a href="frontController.php?controller=point&action=carte">Carte</a>
        <a href="#">Roadmap</a> <a href="#">Docs</a>    </nav>

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

<main <?php echo ($pagetitle === "Carte des points") ? 'class="carte-view"' : ''; ?>>
    <?php require __DIR__ . "/{$cheminVueBody}"; ?>
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

</body>
</html>