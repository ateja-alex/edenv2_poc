<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Tarif', 'valeur' => 'tarif', 'ordre' => 3),
		array('nom' => 'Traité', 'valeur' => 'traite', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [

		array('nom_sql' => 'date'),
		array('nom_sql' => 'traite'),
	],
];