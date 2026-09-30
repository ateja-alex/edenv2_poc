<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Requête', 'valeur' => 'requete', 'ordre' => 1),
		array('nom' => 'Valeur interval', 'valeur' => 'valeur_interval', 'ordre' => 2),
		array('nom' => 'Unité interval', 'valeur' => 'unite_interval', 'ordre' => 3),
		array('nom' => 'Date dernière exécution', 'valeur' => 'derniere_execution', 'ordre' => 4),
		array('nom' => 'Durée dernière exécution', 'valeur' => '#duree_derniere_execution#s', 'ordre' => 5, 'type' => 'concatenation'),
	],
	'calculs' => [],
	'filtres' => [],
];