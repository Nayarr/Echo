<body>
    <h1>Recherche sauvegarder</h1>
    <?php 
    //Faire une base de donnée utilisateur avec la méme syntaque qu'ici

    $IDURL = urlencode($recherche->getID());
    $TitreHTML = htmlspecialchars($recherche->getTitre());
    $DateHTML = htmlspecialchars($recherche->getDate());
    $lienHTML = htmlspecialchars($recherche->getlien());

    ?>

    <ul>
        <li><strong>Titre </strong><br> <?= $TitreHTML?></li>
        <li><strong>Date </strong> <br><?= $DateHTML ?></li>
        <li><strong>Lien </strong> <br><?= $lienHTML ?></li>
    </ul>

</body>
