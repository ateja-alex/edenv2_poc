<?php

return [
		
	'type_element' => 'devis_achat',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',
	
	'colonnes' => [
		
		array('nom' => 'Référence', 'valeur' => 'reference_document', 'ordre' => 0, 'lien_vers_element' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Fournisseur', 'valeur' => 'fournisseur_id', 'ordre' => 3),
		array('nom' => 'HT', 'valeur' => 'montant_document_ht', 'ordre' => 4),
		array('nom' => 'TTC', 'valeur' => 'montant_document_ttc', 'ordre' => 5),
		array('nom' => 'Tags', 'valeur' => '', 'methode' => 'tags_pour_liste', 'ordre' => 6),
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];