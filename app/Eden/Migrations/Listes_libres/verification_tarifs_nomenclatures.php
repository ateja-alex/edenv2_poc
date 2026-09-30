<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Article Nomenclature', 'valeur' => 'article_id_nomenclature', 'lien_vers_autre_element' => 'article_id_nomenclature', 'ordre' => 1),
		array('nom' => 'Article Modifié', 'valeur' => 'article_id_modifie', 'lien_vers_autre_element' => 'article_id_modifie', 'ordre' => 1),
		array('nom' => 'Ancien tarif', 'valeur' => 'ancien_tarif', 'ordre' => 3),
		array('nom' => 'Nouveau tarif', 'valeur' => 'nouveau_tarif', 'ordre' => 3),
	],
	'calculs' => [],
	'filtres' => [

		array('nom_sql' => 'article_id_nomenclature'),
		array('nom_sql' => 'article_id_modifie'),
		array('nom_sql' => 'ancien_tarif'),
		array('nom_sql' => 'nouveau_tarif'),
	],
];