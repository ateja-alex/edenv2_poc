<?php

return [

	"categorie" => "activite_operationnelle",
	"icone" => "table",
	"titre" => "Suivi des factures",
	"description" => "Tableau de suivi du statut des factures",
	"kanban" => "statut",
	"kanban_colonnes" => "[0,5,10]",
	
	"liste_libre" => [
	
		"limit" => "0",
		"orderby" => "date",
		"orderby_sens" => "",
		"type_element" => "facture_vente",
		
		"colonnes" => [],
		'calculs' => [],
		'filtres' => [],
	]
];