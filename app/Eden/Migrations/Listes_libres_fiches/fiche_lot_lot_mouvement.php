<?php

return [
		
	'type_element' => 'lot_mouvement',
	"fiche" => "lot",
	"cle_etrangere" => "lot_id",
	
	'colonnes' => [
		
		array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 0),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
		array('nom' => 'Document', 'valeur' => '', 'methode' => 'liste_lien_document', 'ordre' => 2),
	],
	
	'calculs' => [],
	
	'filtres' => [],
];