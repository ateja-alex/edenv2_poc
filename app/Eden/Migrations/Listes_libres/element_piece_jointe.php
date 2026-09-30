<?php

return [

    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 1),
        array('nom' => 'Type élément', 'valeur' => 'type_element', 'ordre' => 2, 'retour_a_la_ligne_impossible' => 1),
        array('nom' => 'ID élément', 'valeur' => 'element_id', 'ordre' => 3),
        array('nom' => 'Disponible extranet', 'type' => 'champ', 'champ' => 'disponible_extranet', 'ordre' => 4),
    ],
    'calculs' => [],
    'filtres' => [

        array('nom_sql' => 'type_element'),
    ],
];