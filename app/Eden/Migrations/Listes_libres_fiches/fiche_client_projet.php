<?php

return [
		
	'type_element' => 'projet',
    'fiche' => 'client',
    'cle_etrangere' => 'client_id',
	
	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 1),
		array('nom' => 'Valeur', 'valeur' => 'valeur', 'ordre' => 2),
		array('nom' => 'Temps transfo en devis', 'valeur' => '#temps_transfo_premier_devis# jours', 'ordre' => 3, 'type' => 'concatenation'),
		array('nom' => 'Délai de traitement théorique', 'valeur' => '#delai_traitement_theorique# jours', 'ordre' => 4, 'type' => 'concatenation'),
		array('nom' => 'Statut', 'valeur' => 'statut', 'ordre' => 5),
	],
	
	'calculs' => [],
	
	'filtres' => [],
];