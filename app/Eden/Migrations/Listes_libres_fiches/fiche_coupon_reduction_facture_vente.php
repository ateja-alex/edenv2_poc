<?php

return [
		
	'type_element' => 'facture_vente',
	
	'colonnes' => [
		
		array('nom' => 'Référence', 'valeur' => 'reference_document', 'ordre' => 0, 'lien_vers_element' => 1),
        array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
        array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'HT', 'valeur' => 'montant_document_ht', 'ordre' => 3),
	],
	
	'calculs' => [],
	
	'filtres' => [],
];