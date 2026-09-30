<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', "lien_vers_element" => "0", 'ordre' => 0),
        array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
        array('nom' => 'Entrepot de depart', 'valeur' => 'entrepot_depart_id', 'ordre' => 2),
        array('nom' => "Entrepot d'arrivée", 'valeur' => 'entrepot_arrivee_id', 'ordre' => 3),
        array('nom' => "Réservé", 'valeur' => 'reserve', 'ordre' => 3),
	],
	'calculs' => [],
	'filtres' => [],
];