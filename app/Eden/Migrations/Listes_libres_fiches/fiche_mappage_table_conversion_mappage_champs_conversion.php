<?php

return [

    'type_element' => 'mappage_champs_conversion',
    'fiche' => 'mappage_table_conversion',
    'cle_etrangere' => 'mappage_table',

    'colonnes' => [

        array('nom' => 'Champ de départ', 'valeur' => 'champ_depart', 'ordre' => 0),
        array('nom' => 'Champ d\'arrivée', 'valeur' => 'champ_arrivee', 'ordre' => 1),
        array('nom' => 'Type élément d\'arrivée', 'valeur' => 'type_element_arrivee', 'ordre' => 2),
        array('nom' => 'Champ de liaison', 'valeur' => 'champ_liaison', 'ordre' => 3),
    ],

    'calculs' => [],
    'filtres' => [],
];