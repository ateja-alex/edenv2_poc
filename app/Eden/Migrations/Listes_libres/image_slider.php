<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Image', 'valeur' => 'image', 'ordre' => 1),
		array('nom' => 'Période', 'valeur' => 'Du #date_debut# au #date_fin#', 'ordre' => 2, 'type' => 'concatenation'),
	],
	'calculs' => [],
	'filtres' => [],
];