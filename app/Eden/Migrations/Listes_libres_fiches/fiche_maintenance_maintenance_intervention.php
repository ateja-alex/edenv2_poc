<?php

return [
		
	'type_element' => 'maintenance_intervention',
    'fiche' => 'maintenance',
    'cle_etrangere' => 'maintenance_id',
	
	'colonnes' => [
		
		array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Date',  'valeur' => 'date', 'ordre' => 1),
		array('nom' => 'Statut',  'valeur' => 'statut', 'ordre' => 2),
		array('nom' => 'Facture',  'valeur' => 'facture_vente_id', 'lien_vers_autre_element' => 'facture_vente_id', 'ordre' => 3),
	],
	'calculs' => [],
	'filtres' => [],
];