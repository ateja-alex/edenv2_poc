<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Client', 'valeur' => '','methode' => 'recuperer_client', 'ordre' => 1),
		array('nom' => 'Description', 'valeur' => 'description', 'ordre' => 2),
		array('nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 3),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 4),
		array('nom' => 'Type', 'valeur' => 'type', 'ordre' => 5),
		
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'utilisateur_id'),
		array('nom_sql' => 'date'),
		array('nom_sql' => 'type'),
	],
];