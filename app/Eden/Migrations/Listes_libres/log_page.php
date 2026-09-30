<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'URL', 'valeur' => '#url# (#methode#)', 'ordre' => 3, 'type' => 'concatenation'),
	],
	'calculs' => [
		
		array('nom_sql' => 'id', 'type_calcul' => 'COUNT', 'nom' => 'Pages affichées', 'split' => 'utilisateur_id', 'unite' => 'page', ),
	],
	'filtres' => [
		
		array('nom_sql' => 'date'),
		array('nom_sql' => 'utilisateur_id'),
	],
];