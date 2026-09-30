<?php


return [

    'colonnes' => [
        array('nom' => 'Type', 'valeur' => 'type', 'ordre' => 1),
        array('nom' => 'Niveau', 'valeur' => 'niveau', 'ordre' => 2),
        array('nom' => 'Valeur', 'valeur' => '', 'methode' => "valeur_liste", 'ordre' => 3),
    ],

    'calculs' => [],

    'filtres' => [
        array('nom_sql' => 'type'),
    ],
];