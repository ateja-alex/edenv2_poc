<?php

return [

	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
		array('nom' => 'Article enfant', 'valeur' => 'article_enfant_id', 'ordre' => 2),
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 3),
		array('nom' => 'Tarif', 'valeur' => 'tarif', 'ordre' => 4),
		array('nom' => "Prix d'achat", 'valeur' => 'prix_achat', 'ordre' => 4),
		array('nom' => 'Conditionnement', 'valeur' => 'conditionnement', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [
		array('nom_sql' => 'article_id'),
		array('nom_sql' => 'article_enfant_id'),
	],
];