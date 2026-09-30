<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
		array('nom' => 'Document', 'methode' => 'liste_lien_document', 'valeur' => '', 'ordre' => 2),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 3),
		array('nom' => 'A faire', 'valeur' => 'a_faire', 'ordre' => 4),
	],
	'calculs' => [
	
		array('nom_sql' => 'id', 'type_calcul' => 'COUNT', 'nom' => 'Relances à faire', 'split' => 'a_faire', 'unite' => 'relances', ),
	],
	'filtres' => [
		
		array('nom_sql' => 'a_faire'),
	],
];