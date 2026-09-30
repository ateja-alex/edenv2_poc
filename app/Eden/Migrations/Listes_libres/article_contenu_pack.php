<?php

return [

	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
		array('nom' => 'Article enfant', 'valeur' => 'article_enfant_id', 'ordre' => 2),
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 3),
		array('nom' => 'Ordre', 'valeur' => 'ordre', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [
		array('nom_sql' => 'article_id'),
		array('nom_sql' => 'article_enfant_id'),
	],
];