<?php

return [

	'type_element' => 'article_fournisseur',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',

	'colonnes' => [

		array('nom' => 'Fournisseur', 'valeur' => 'fournisseur_id', 'ordre' => 0, 'lien_vers_element' => 1),
		array('nom' => 'Référence', 'valeur' => 'reference', 'ordre' => 2),
		array('nom' => 'Conditionnement', 'valeur' => 'conditionnement_id', 'ordre' => 3),
		array('nom' => 'Prioritaire', 'valeur' => 'fournisseur_prioritaire', 'ordre' => 4),
	],

	'calculs' => [],

	'filtres' => [

		array('nom_sql' => 'fournisseur_id'),
		array('nom_sql' => 'fournisseur_prioritaire'),
	],
];
