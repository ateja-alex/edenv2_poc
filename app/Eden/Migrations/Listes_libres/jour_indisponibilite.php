<?php

return [

	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 1),
		array('nom' => 'Date de début', 'valeur' => 'date_debut', 'ordre' => 2),
		array('nom' => 'Date de fin', 'valeur' => 'date_fin', 'ordre' => 3),
		array('nom' => "Type d'indisponiblité", 'valeur' => 'type_indisponibilite', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [
        array('nom_sql' => 'type_indisponibilite'),
    ],
];