<?php

return [
		
	'type_element' => 'nature_article_modele_marge',
    'fiche' => 'nature_article',
    'cle_etrangere' => 'nature_article_id',
	
	'colonnes' => [
		
		array('nom' => 'Modèle', 'valeur' => 'nature_article_modele_id', 'ordre' => 0,),
		array('nom' => 'Marge', 'valeur' => 'marge', 'ordre' => 1),
	],
	
	'calculs' => [],
	'filtres' => [],
];