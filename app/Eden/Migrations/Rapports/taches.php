<?php
			return ["id_rapport" => "taches",
	"categorie" => "crm",
	"icone" => "table",
	"titre" => "Tâches",
	"type_rapport" => "liste_libre",
	"type_element" => "tache",
	"liste_libre" => [
		"type_element" => "tache",
		"id_rapport" => "taches",
		'colonnes' => [
					array("liste_libre_id" => "323", "nom" => "Date", "valeur" => "date_de_debut", "ordre" => "1", "type" => "standard", ),
						array("liste_libre_id" => "323", "nom" => "Statut", "ordre" => "2", "type" => "champ", "champ" => "terminee", ),
						],
				'calculs' => [
					],
				'filtres' => [
					],
				]];