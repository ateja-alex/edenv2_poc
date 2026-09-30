<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Document', 'valeur' => '', 'methode' => 'affiche_element_lie', 'ordre' => 1),
		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 2),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 3),
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 4),
		array('nom' => 'Entrepôt', 'valeur' => 'entrepot_id', 'ordre' => 5),
		array('nom' => 'Type de mouvement', 'valeur' => 'type_de_mouvement', 'ordre' => 6),
		array('nom' => 'Tags', 'valeur' => '', 'methode' => 'liste_tags', 'ordre' => 7),
	],
	'calculs' => [],
	'filtres' => [],
];