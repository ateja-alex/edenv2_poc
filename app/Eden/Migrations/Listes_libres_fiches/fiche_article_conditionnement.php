<?php

return [

    'type_element' => 'conditionnement',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',

    'colonnes' => [

        array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 1),
        array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 2),
        array('nom' => 'Tarif', 'valeur' => 'tarif', 'ordre' => 3),
        array('nom' => "Prix d'achat", 'valeur' => 'prix_achat', 'ordre' => 4),
    ],

    'calculs' => [],

    'filtres' => [],
];