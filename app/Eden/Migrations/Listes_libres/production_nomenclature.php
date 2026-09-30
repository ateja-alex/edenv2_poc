<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', "lien_vers_element" => "0", 'ordre' => 0),
		array('nom' => 'Article', 'valeur' => 'article', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Quantite', 'valeur' => 'quantite', 'ordre' => 3),
		array('nom' => 'Entrepot', 'valeur' => 'entrepot', 'ordre' => 4),
	],

	'calculs' => [],

	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];