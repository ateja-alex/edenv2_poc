<?php

return [

	'type_element' => 'bon_retour_vente',
    'fiche' => 'client',
    'cle_etrangere' => 'client_id',

	'colonnes' => [

		array('nom' => 'Référence', 'valeur' => 'reference_document', 'ordre' => 0, 'lien_vers_element' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
		array('nom' => 'Commentaires', 'valeur' => 'commentaires', 'ordre' => 5),
		array('nom' => 'Tags', 'valeur' => '', 'methode' => 'tags_pour_liste', 'ordre' => 6),
	],

	'calculs' => [],

	'filtres' => [

		array('nom_sql' => 'date'),
	],
];