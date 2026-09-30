<?php

return [

	'colonnes' => [

		['nom' => '#', 'valeur' => 'id', 'ordre' => 0],
		['nom' => 'Projet', 'valeur' => 'projet_id', 'ordre' => 1],
		['nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 2],
		['nom' => 'Date', 'valeur' => 'date', 'ordre' => 3],
		['nom' => 'Durée', 'valeur' => 'duree', 'ordre' => 4],
	],

	'calculs' => [],
	
	'filtres' => [
		['nom_sql' => 'date'],
		['nom_sql' => 'utilisateur_id'],
	],
];