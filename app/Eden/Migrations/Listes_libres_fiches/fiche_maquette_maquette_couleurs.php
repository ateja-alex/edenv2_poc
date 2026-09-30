<?php

return [

    'type_element' => 'maquette_couleurs',
    'fiche' => 'maquette',
    'cle_etrangere' => 'maquette',

    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Nom de la couleur', 'valeur' => 'nom_couleur', 'ordre' => 1),
        array('nom' => 'Valeur', 'valeur' => 'valeur', 'ordre' => 2),
        array('nom' => 'Valeurs', 'valeur' => 'valeurs', 'ordre' => 3),
    ],

    'calculs' => [],
    'filtres' => [

        array('nom_sql' => 'nom_couleur'),
    ],
];