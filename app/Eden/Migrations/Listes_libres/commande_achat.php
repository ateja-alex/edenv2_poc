<?php

return [
	'colonnes' => [
		['nom' => '#', 'valeur' => 'id', 'ordre' => 0],
		['nom' => 'Fournisseur', 'valeur' => 'fournisseur_id', 'ordre' => 1],
		['nom' => 'Date', 'valeur' => 'date', 'ordre' => 2],
		['nom' => 'HT', 'valeur' => 'montant_document_ht', 'ordre' => 3, 'retour_a_la_ligne_impossible' => 1],
		['nom' => 'Référence document', 'valeur' => 'reference_document', 'ordre' => 4],
	],
	'calculs' => [
        [
            'nom_sql'     => 'montant_document_ht',
            'type_calcul' => 'SUM',
            'nom'         => 'CA HT',
            'split'       => '',
            'unite'       => '€', 
			
        ],
	],
	'filtres' => [

		['nom_sql' => 'date'],
	],
];
