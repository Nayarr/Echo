<body>
    <h1>Détails de la utilisateur</h1>
    <?php 
    //Faire une base de donnée utilisateur avec la méme syntaque qu'ici

    $loginURL = urlencode($utilisateur->getLogin());
    $nomHTML = htmlspecialchars($utilisateur->getNom());
    $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
    $emailHTML = htmlspecialchars($utilisateur->getEmail());
    $ProfilHTML = htmlspecialchars($utilisateur->getProfil());

    ?>

    <ul>
        <li><strong>Nom </strong> <br><?= $nomHTML?></li>
        <li><strong>Prenom </strong><br> <?= $prenomHTML ?></li>
        <li><strong>Email </strong> <br><?= $emailHTML ?></li>
        <li><strong>Profil </strong><br> <?= $ProfilHTML ?></li>
    </ul>
    <?php 
     //refaire le lien 
            echo '<a href="../web/frontController.php?controller=utilisateur&action=update&login=' . $loginURL .'">
                    <button class="button_modifier">Modifier</button>
            </a>';
        
        ?>
</body>
