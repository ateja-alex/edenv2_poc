<?php

return [

	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Type élément', 'valeur' => 'type_element', 'ordre' => 1),
		array('nom' => 'Element id', 'valeur' => 'element_id', 'ordre' => 2),
		array('nom' => 'Date de début', 'valeur' => 'date_debut', 'ordre' => 3),
		array('nom' => 'Date de fin', 'valeur' => 'date_fin', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [
        array('nom_sql' => 'type_element'),
    ],
];