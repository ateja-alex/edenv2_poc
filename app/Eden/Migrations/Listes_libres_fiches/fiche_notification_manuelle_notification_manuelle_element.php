<?php

return [

	'type_element' => 'notification_manuelle_element',
    'fiche' => 'notification_manuelle',
    'cle_etrangere' => 'notification_manuelle_id',

	'colonnes' => [

		array('nom' => 'Element ID','valeur' => '', 'methode' => 'recuperer_element_via_id', 'ordre' => 0),
		array('nom' => 'Envoyee', 'valeur' => 'envoyee', 'ordre' => 1),
		array('nom' => "Erreur lors de l'envoi", 'valeur' => '','methode' => 'affichage_erreur', 'ordre' => 2),
	],

	'calculs' => [],

	'filtres' => [

		array('nom_sql' => 'envoyee'),
	],
];