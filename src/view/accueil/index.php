<body>

    <div class="page1">
        <video autoplay muted loop playsinline class="video-bg">
        <source src="/Echo/web/assets/video/Wavy.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo HTML5.
        </video>

        <div class="video-overlay"></div>

        <div class="titre">
            <img width="40%" height="auto" src="/Echo/web/assets/img/Logo4.png" alt="Main Logo Site" class="Logo1">
        </div>

        <div class="sous_titre">
            <img width="35%" height="auto" src="/Echo/web/assets/img/Logo4Subtitle.png" alt="Sous-Titre Logo" class="Logo">
        </div>

        <div class="accueilbtn">
            <a href="<?= $baseURL ?>?action=carte&controller=point" class="button type1">
                <span class="btn-txt">Accéder à la carte</span>
            </a>


        </div>
    </div>

    <section class="section-wrap">

    <!-- Bloc 1 : Intro + Planisphere -->
    <div class="glass-panel grid-2">
        <div class="panel-text">
        <h2>L'Acidification des Océans :<br>Rendre Visible l'Invisible</h2>
        <p>
            L'océan est souvent perçu comme une masse d'eau immuable, mais sous la surface, une crise silencieuse bouleverse
            l'équilibre chimique de notre planète. Ce site de visualisation de données est né d'une volonté simple :
            comprendre et illustrer l'impact du CO<sub>2</sub> sur nos écosystèmes marins.
        </p>
        <p>
            Dans le cadre de notre projet de deuxième année en BUT Informatique (SAE 3.01), notre équipe s'est penchée sur
            ce phénomène complexe pour le rendre accessible à travers la data.
        </p>
        </div>

        <div class="panel-media">
        <img src="/Echo/web/assets/img/Planisphere.png" alt="Planisphère">
        </div>
    </div>

    <!-- Bloc 2 : PH image + Pourquoi -->
    <div class="glass-panel grid-2 reverse">
        <div class="panel-media">
        <img src="/Echo/web/assets/img/Ph_Eau.png" alt="pH et vie marine">
        </div>

        <div class="panel-text">
        <h3>Pourquoi parler d'acidification ?</h3>
        <p>
            L'acidification des océans est une conséquence directe de nos émissions de gaz à effet de serre.
            Chaque année, les océans absorbent environ 30% du CO<sub>2</sub> émis par les activités humaines.
        </p>

        <ul class="bullet">
            <li><strong>Une chimie bouleversée :</strong> le CO<sub>2</sub> dissous forme de l'acide carbonique et fait baisser le pH.</li>
            <li><strong>Une acidité en hausse :</strong> depuis l’ère industrielle, l’acidité a augmenté d’environ 30%.</li>
            <li><strong>Une menace biologique :</strong> coraux/coquilles en difficulté, impacts sur la chaîne alimentaire.</li>
        </ul>

        <p class="small">
            Ce n’est pas qu’un problème écologique : des milliards de personnes dépendent de la mer (pêche, tourisme).
        </p>
        </div>
    </div>

    <!-- Titre section cards -->
    <h3 class="section-title">Ce que ce site vous permet de visualiser</h3>

    <!-- Bloc cartes -->
    <div class="glass-panel cards-panel">
        <div class="cards-grid">

        <article class="feature-card active">
            <div class="icon-ring">
            <span class="icon">PH</span>
            </div>
            <h4>PH (Soon)</h4>
            <p>Mesures directes de l'acidité et de la concentration en carbone.</p>
        </article>

        <article class="feature-card">
            <div class="icon-ring">
            <span class="icon">💧</span>
            </div>
            <h4>La Salinité</h4>
            <p>Indicateur de la capacité physique de l'eau à dissoudre les gaz.</p>
        </article>

        <article class="feature-card active">
            <div class="icon-ring">
            <span class="icon">🌡️</span>
            </div>
            <h4>La Température</h4>
            <p>Facteur clé influençant la solubilité et l’absorption du CO2.</p>
        </article>

        <article class="feature-card">
            <div class="icon-ring">
            <span class="icon">O2</span>
            </div>
            <h4>Chlorophylle-a et Oxygène dissous (Soon)</h4>
            <p>Évaluer l’impact sur la vie marine et les risques d’hypoxie.</p>
        </article>

        </div>
    </div>

    <h3 class="section-title">La transparence de nos données</h3>
    <div class="glass-panel">
        <div class="panel-text">
        <p>
            Toutes les visualisations présentes sur ce site sont alimentées par le Copernicus Marine Service (CMEMS),
            une référence mondiale offrant des données validées scientifiquement.
        </p>
        </div>
    </div>

    </section>


</body>