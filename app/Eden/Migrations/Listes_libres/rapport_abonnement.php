<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 1),
		array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 2),
		array('nom' => 'Fréquence', 'valeur' => 'frequence_detail', 'ordre' => 3),
	],
	'calculs' => [
	],
	'filtres' => [
		
		array('nom_sql' => 'utilisateur_id'),
	],
];