<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Acidification des Océans</title>
</head>
<body>

    <h1>L'Acidification des Océans : Rendre Visible l'Invisible</h1>

    <p>
        L'océan est souvent perçu comme une masse d'eau immuable, mais sous la surface, une crise silencieuse bouleverse l'équilibre chimique de notre planète. Ce site de visualisation de données est né d'une volonté simple : comprendre et illustrer l'impact du CO<sub>2</sub> sur nos écosystèmes marins.
    </p>

    <p>
        Dans le cadre de notre projet de deuxième année en BUT Informatique (SAE 3.01), notre équipe s'est penchée sur ce phénomène complexe pour le rendre accessible à travers la data.
    </p>

    <h2>Pourquoi parler d'acidification ?</h2>

    <p>
        L'acidification des océans est une conséquence directe de nos émissions de gaz à effet de serre. Chaque année, les océans absorbent environ 30 % du CO<sub>2</sub> émis par les activités humaines. C'est un service écologique immense, mais qui a un coût :
    </p>

    <ul>
        <li>
            <strong>Une chimie bouleversée :</strong> Lorsque le CO<sub>2</sub> se dissout, il forme de l'acide carbonique, libérant des ions hydrogène qui font baisser le pH de l'eau.
        </li>
        <li>
            <strong>Une acidité en hausse :</strong> Depuis l'ère industrielle, le pH moyen de surface est passé de 8,2 à 8,1. Cela semble peu, mais c'est une échelle logarithmique : cela représente une augmentation de l'acidité de 30 %.
        </li>
        <li>
            <strong>Une menace biologique :</strong> Cette acidité "ronge" les éléments calcaires. Les coraux, huîtres et crabes peinent à fabriquer leurs coquilles, et le phytoplancton, base de la chaîne alimentaire, voit sa productivité chuter.
        </li>
    </ul>

    <p>
        Ce n'est pas qu'un problème écologique : plus de 3 milliards de personnes dépendent de la mer pour vivre (pêche, tourisme).
    </p>

    <h2>Ce que ce site vous permet de visualiser</h2>

    <p>
        Pour comprendre ce phénomène, il ne suffit pas de regarder le pH. Notre outil croise plusieurs indicateurs clés que nous avons sélectionnés pour leur pertinence scientifique :
    </p>

    <ol>
        <li>
            <strong>Le pH et le CO<sub>2</sub> :</strong> Les mesures directes de l'acidification.
        </li>
        <li>
            <strong>La Température :</strong> Car l'eau chaude absorbe moins bien le CO<sub>2</sub>.
        </li>
        <li>
            <strong>La Salinité :</strong> Qui influence la capacité de l'eau à dissoudre les gaz.
        </li>
        <li>
            <strong>La Chlorophylle-a et l'Oxygène dissous :</strong> Pour visualiser l'impact concret sur la vie marine (phytoplancton) et les zones mortes (hypoxie).
        </li>
    </ol>

    <p>
        <button type="button">Visualiser les données</button>
    </p>

    <h2>La transparence de nos données</h2>

    <p>
        À l'heure des fake news, la source des données est primordiale. Nous avons fait le choix de la rigueur scientifique plutôt que de la facilité.
    </p>

    <p>
        Toutes les visualisations présentes sur ce site sont alimentées par le <strong>Copernicus Marine Service (CMEMS)</strong>. Il s'agit d'une référence mondiale offrant des données validées scientifiquement.
    </p>

    <p>
        Bien que d'autres sources existent (comme les API météo classiques ou StormGlass), nous les avons écartées car elles étaient soit payantes, soit moins complètes scientifiquement. Utiliser Copernicus nous a demandé de relever un défi technique important : le traitement de formats complexes (NetCDF) pour garantir que chaque graphique que vous voyez est basé sur des relevés océanographiques fiables.
    </p>

    <h2>Notre équipe</h2>

    <p>
        Ce projet est réalisé par une équipe de 6 étudiants de l'UPEC (IUT Sénart-Fontainebleau) :<br>
        Rayan Oughlis, Chem'S-Edin Kamboua,  Adam Rakibi, Hanah Sahmoune, et Yoni Vaysse.
    </p>

    <p>
        Notre objectif ? Transformer des gigaoctets de données brutes en une prise de conscience réelle.
    </p>

</body>
</html>