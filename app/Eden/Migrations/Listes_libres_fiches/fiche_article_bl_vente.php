<?php

return [
		
	'type_element' => 'bl_vente',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',
	
	'colonnes' => [
		
		array('nom' => 'Référence', 'valeur' => 'reference_document', 'ordre' => 0, 'lien_vers_element' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 3),
		array('nom' => 'Commentaires', 'valeur' => 'commentaires', 'ordre' => 4),
		array('nom' => 'Tags', 'valeur' => '', 'methode' => 'tags_pour_liste', 'ordre' => 5),
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];