<?php

return [
		
	'type_element' => 'avoir_achat_lignes',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',
	
	'colonnes' => [
		
		array('nom' => 'Document', 'valeur' => 'document_id', 'ordre' => 0, 'lien_vers_element' => 1),
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 2),
		array('nom' => 'PU', 'valeur' => 'tarif', 'ordre' => 3),
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'quantite'),
		array('nom_sql' => 'tarif'),
	],
];