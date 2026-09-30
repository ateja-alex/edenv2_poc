<?php

return [

	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Nom du trigger', 'valeur' => 'nom' , 'ordre' => 1),
		array('nom' => 'Type élément origine', 'valeur' => 'type_element_id', 'ordre' => 2),
		array('nom' => 'Type élément concerné', 'valeur' => 'type_element_concerne_id', 'ordre' => 3),
		array('nom' => "Erreur d'exécution", 'valeur' => 'erreur_requete', 'ordre' => 5),
		array('nom' => "Durée d'exécution", 'valeur' => 'duree_derniere_execution', 'ordre' => 6),
		array('nom' => "Date de la dernière exécution", 'valeur' => 'date_derniere_execution', 'ordre' => 7),
		array('nom' => "Ordre", 'valeur' => 'ordre', 'ordre' => 8),
	],
	'calculs' => [],
	'filtres' => [
		array('nom_sql' => 'type_element_id'),
		array('nom_sql' => 'type_element_concerne_id'),
	],
];