<?php

return [

	'categorie' => 'gestion_projet',
	'icone' => 'table',
	'titre' => 'Suivi des interventions annuelles',
	'description' => "Suivi des interventions annuelles (tableau kanban)",
	'ordre' => 5,
	'inactif' => 0,
	'kanban' => 'statut',
	'kanban_colonnes' => [1,2,3],
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'maintenance_intervention',
		
		'colonnes' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date']],
	],
];