<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Contrat de maintenance', 'valeur' => 'maintenance_id', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Statut', 'valeur' => 'statut', 'ordre' => 3),
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];