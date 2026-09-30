<?php

return [

    'type_element' => 'export_compta_colonne',
    'fiche' => 'export_compta_modele',
    'cle_etrangere' => 'export_compta_modele_id',

    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Titre', 'valeur' => 'titre', 'ordre' => 1),
        array('nom' => 'Valeur', 'valeur' => 'valeur', 'ordre' => 2),
        array('nom' => 'Ordre', 'valeur' => 'ordre', 'ordre' => 3),
    ],

    'calculs' => [],

    'filtres' => [],
];