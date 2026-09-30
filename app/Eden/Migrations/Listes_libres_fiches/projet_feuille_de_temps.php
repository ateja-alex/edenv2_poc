<?php

return [
		
	'type_element' => 'feuille_de_temps',
    'fiche' => 'projet',
    'cle_etrangere' => 'element_id',

	'colonnes' => [

		['nom' => '#', 'valeur' => 'id', 'ordre' => 0],
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