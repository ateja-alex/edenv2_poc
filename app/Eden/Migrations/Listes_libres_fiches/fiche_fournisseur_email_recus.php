<?php

return [
		
	'type_element' => 'email_recus',
    'fiche' => 'fournisseur',
    'cle_etrangere' => 'fournisseur_id',
	
	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Sujet', 'valeur' => 'sujet', 'ordre' => 1),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
		array('nom' => 'Texte', 'valeur' => 'texte', 'ordre' => 7),
		array('nom' => 'Pièces jointes', 'valeur' => '', 'methode' => 'colonne_affichage_pieces_jointes', 'ordre' => 8),
		array('nom' => 'Projet ID', 'lien_vers_autre_element' => 'projet_id', 'valeur' => 'projet_id', 'ordre' => 11),
		array('nom' => 'Traité', 'valeur' => 'traite', 'ordre' => 15),
		array('nom' => 'En charge', 'valeur' => '#en_charge#', 'ordre' => 16),
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'date'),
	],
];