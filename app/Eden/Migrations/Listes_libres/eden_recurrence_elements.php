<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Mode', 'valeur' => 'mode_recurrence', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'rdi_date_generation', 'ordre' => 2),
		array('nom' => 'Modèle', 'valeur' => 'rdi_id_modele', 'ordre' => 3),
		array('nom' => 'Prochaine occurence', 'valeur' => 'rdi_prochaine_occurence', 'ordre' => 3),
		array('nom' => 'Tags', 'valeur' => '', 'methode' => 'liste_tags', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'inactif'),
	],
];