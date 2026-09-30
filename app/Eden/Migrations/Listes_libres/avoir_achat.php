<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Fournisseur', 'valeur' => 'fournisseur_id', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'HT', 'valeur' => 'montant_document_ht', 'ordre' => 3, 'retour_a_la_ligne_impossible' => 1),
	],
	'calculs' => [
		
		array('nom_sql' => 'montant_document_ht', 'type_calcul' => 'SUM', 'nom' => 'CA HT', 'split' => '', 'unite' => '€', ),
	],
	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];