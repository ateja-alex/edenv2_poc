<?php

return [

    'type_element' => 'montant_eco_contribution',
    'fiche' => 'categorie_eco_contribution',
    'cle_etrangere' => 'categorie_eco_contribution_id',

    'colonnes' => [

        array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 1),
        array('nom' => 'Date de début', 'valeur' => 'date_debut', 'ordre' => 2),
        array('nom' => 'Date de fin', 'valeur' => 'date_fin', 'ordre' => 3)
    ],

    'calculs' => [],

    'filtres' => [],
];