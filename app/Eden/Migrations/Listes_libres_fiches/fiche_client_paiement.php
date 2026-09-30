<?php

return [
		
	'type_element' => 'paiement',
    'fiche' => 'client',
    'cle_etrangere' => 'client_id',
	
	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
		array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 2),
		array('nom' => 'Mode', 'valeur' => 'mode_paiement_id', 'ordre' => 3),
		array('nom' => 'Document', 'valeur' => '', 'methode' => 'liste_reference_document', 'ordre' => 4),
		
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'date'),
		array('nom_sql' => 'mode_paiement_id'),
	],
];