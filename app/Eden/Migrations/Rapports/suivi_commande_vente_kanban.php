<?php

return [

	"categorie" => "activite_operationnelle",
	"icone" => "table",
	"titre" => "Suivi des commandes",
	"description" => "Tableau de suivi du statut des commandes",
	"kanban" => "statut",
	"kanban_colonnes" => "[0,3,5,10,15,20,25,30,40,43,45]",
	
	"liste_libre" => [
	
		"limit" => "0",
		"orderby" => "date",
		"orderby_sens" => "",
		"type_element" => "commande_vente",
		
		"colonnes" => [],
		'calculs' => [],
		'filtres' => [],
	]
];