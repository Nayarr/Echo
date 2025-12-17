<body>

    <div class="background"></div>
    <div class="vagues">
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 1500 660"
        fill="none"
        class="wave-svg"
    >
        <path d="M0 68.4169C0 68.4169 243.56 -25.9033 370.635 26.2738C497.71 78.451 621.671 94.0225 758.09 68.4169C894.508 42.8113 972.996 -41.4561 1131.84 26.2738C1290.68 94.0037 1495 68.4169 1495 68.4169" stroke="white"/>
        <path d="M1494.43 185.645C1494.43 185.645 1250.03 119.712 1123.65 160.169C997.279 200.627 873.604 214.01 737.019 197.18C600.433 180.351 521.068 119.328 363.127 171.752C205.186 224.176 0.763659 208.393 0.763659 208.393" stroke="white"/>
        <path d="M11 364.417C11 364.417 254.56 270.097 381.635 322.274C508.71 374.451 632.671 390.023 769.09 364.417C905.508 338.811 983.996 254.544 1142.84 322.274C1301.68 390.004 1506 364.417 1506 364.417" stroke="white"/>
    </svg>
    </div>

    <div class="page1">
        <div class="titre">
            <h1>Echo</h1>
        </div>

        <div class="sous_titre">
            <h3>Votre solution de visualisation de données marine</h3>
        </div>

        <div class="accueilbtn">
            <a href="<?= $baseURL ?>?action=carte&controller=point" class="signin">Carte</a>
            
            <a href="<?= $baseURL ?>?action=accueil" class="signup">Sign In</a>
        </div>
    </div>

    <div class="bulles"></div>

    <div class="conteneur">
        <div class="boite_texte">
            <div id="intro">
                <h3>L'Acidification des Océans : Rendre Visible l'Invisible</h3>
                <p>
                    L'océan est souvent perçu comme une masse d'eau immuable, mais sous la surface, une crise silencieuse bouleverse l'équilibre chimique de notre planète. Ce site de visualisation de données est né d'une volonté simple : comprendre et illustrer l'impact du CO<sub>2</sub> sur nos écosystèmes marins.
                <p>
                    Dans le cadre de notre projet de deuxième année en BUT Informatique (SAE 3.01), notre équipe s'est penchée sur ce phénomène complexe pour le rendre accessible à travers la data.
                </p>
            </div>

            <div id="pourquoi">
                <h4>Pourquoi parler d'acidification ?</h4>
                <p>
                    L'acidification des océans est une conséquence directe de nos émissions de gaz à effet de serre.
                    Chaque année, les océans absorbent environ 30% du CO<sub>2</sub> émis par les activités humaines.
                    C'est un service écologique immense, mais qui a un coût :
                </p>

                <ul>
                <li>
                    Une chimie bouleversée :
                    Lorsque le CO<sub>2</sub> se dissout, il forme de l'acide carbonique, libérant des ions hydrogène
                    qui font baisser le pH de l'eau.
                </li>
                <li>
                    Une acidité en hausse :
                    Depuis l'ère industrielle, le pH moyen de surface est passé de 8,2 à 8,1.
                    Cela semble peu, mais c'est une échelle logarithmique : cela représente une augmentation
                    de l'acidité de 30%.
                </li>
                <li>
                    Une menace biologique :
                    Cette acidité "ronge" les éléments calcaires. Les coraux, huîtres et crabes peinent à fabriquer
                    leurs coquilles, et le phytoplancton, base de la chaîne alimentaire, voit sa productivité chuter.
                </li>
                </ul>

                <p>
                    Ce n'est pas qu'un problème écologique : plus de 3 milliards de personnes
                    dépendent de la mer pour vivre (pêche, tourisme).
                </p>
            </div>

            <div id="donnees">
                <h4>Ce que ce site vous permet de visualiser</h4>
                <p>
                    Pour comprendre ce phénomène, il ne suffit pas de regarder le pH. 
                    Notre outil croise plusieurs indicateurs clés que nous avons sélectionnés pour leur pertinence scientifique:
                </p>

                <ul>
                    <li>
                        Le pH et le CO<sub>2</sub> : Les mesures directes de l'acidification.
                    </li>
                    <li>
                        La Température : Car l'eau chaude absorbe moins bien le CO<sub>2</sub>.
                    </li>
                    <li>
                        La Salinité : Qui influence la capacité de l'eau à dissoudre les gaz.
                    </li>

                    <li>
                        La Chlorophylle-a et l'Oxygène dissous : Pour visualiser l'impact concret sur la vie marine (phytoplancton) et les zones mortes (hypoxie).
                    </li>
                </ul>

                <p>
                    En combinant ces données, notre site offre une vision globale de l'acidification
                    et de ses conséquences sur les écosystèmes marins.
                </p>

                <a href="<?= $baseURL ?>?action=carte&controller=point" class="signin">Visualiser les données</a>
            </div>

            <div id="transparence">
                <h4>La transparence de nos données</h4>
                <p>
                    À l'heure des fake news, la source des données est primordiale. Nous avons fait le choix de la rigueur scientifique plutôt que de la facilité.
                </p>

                <p>
                    Toutes les visualisations présentes sur ce site sont alimentées par le Copernicus Marine Service (CMEMS). Il s'agit d'une référence mondiale offrant des données validées scientifiquement.
                    En utilisant ces données ouvertes, nous garantissons la fiabilité et la transparence de notre travail.
                </p>

                <p>
                   Bien que d'autres sources existent (comme les API météo classiques ou StormGlass), nous les avons écartées car elles étaient soit payantes, soit moins complètes scientifiquement.
                   Utiliser Copernicus nous a demandé de relever un défi technique important : 
                   le traitement de formats complexes (NetCDF) pour garantir que chaque graphique que vous voyez est basé sur des relevés océanographiques fiables
                </p>
            </div>

            <div id="equipe">
                <h4>Notre équipe</h4>
                <p>
                    Ce projet a été réalisé par une équipe de cinq étudiants en deuxième année de BUT Informatique à l'IUT de Créteil Vitry :
                </p>
            </div>
        </div>
    </div>


</body>