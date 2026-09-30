<?php

return [

    'type_element' => 'transformation_document_temps_mappage',
    'fiche' => 'transformation_document_temps_modele',
    'cle_etrangere' => 'modele_id',

    'colonnes' => [

        array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Champ d\'origine',  'valeur' => 'champ_source', 'ordre' => 1),
        array('nom' => 'Champ de destination',  'valeur' => 'champ_destination', 'ordre' => 2),
    ],
    'calculs' => [],
    'filtres' => [],
];