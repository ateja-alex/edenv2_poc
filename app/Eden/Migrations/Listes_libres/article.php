<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Désignation', 'valeur' => 'designation', 'ordre' => 1),
		array('nom' => 'Tarif', 'valeur' => 'tarif', 'ordre' => 2, 'retour_a_la_ligne_impossible' => 1),
		array('nom' => 'Famille', 'valeur' => 'famille_id', 'ordre' => 3),
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'famille_id'),
	],
];