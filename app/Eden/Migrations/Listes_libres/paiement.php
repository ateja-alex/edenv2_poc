<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
		array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 2, 'retour_a_la_ligne_impossible' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 3),
		array('nom' => 'Mode de paiement', 'valeur' => 'mode_paiement_id', 'ordre' => 4),
		array('nom' => 'Compte bancaire', 'valeur' => 'compte_bancaire_id', 'ordre' => 5),
	],
	'calculs' => [],
	'filtres' => [],
];