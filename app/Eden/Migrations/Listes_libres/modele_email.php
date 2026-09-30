<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Type élément', 'valeur' => 'type_element_id', 'ordre' => 1),
		array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 2),
		array('nom' => 'Catégorie', 'valeur' => 'categorie', 'ordre' => 3),
		array('nom' => 'Langue', 'valeur' => 'langue', 'ordre' => 4),
		array('nom' => 'Sujet', 'valeur' => 'sujet_modele', 'ordre' => 5),
		array('nom' => 'Par défaut', 'type' => 'champ', 'champ' => 'par_defaut', 'ordre' => 6)
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'categorie')
	],
];