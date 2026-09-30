<?php

	return [
	
		"type_element" => "proposition_commerciale",
		"fiche" => "lead",
		"cle_etrangere" => "lead_id",
		
		"colonnes" => [
			array("nom" => "#", "valeur" => "id", "ordre" => "1",  "type" => "standard"),
			array("nom" => "Date", "valeur" => "date", "ordre" => "2",  "type" => "standard"),
			array("nom" => "Statut", "valeur" => "statut", "ordre" => "3",  "type" => "standard"),
			array("nom" => "Réponse", "valeur" => "reponse", "ordre" => "4",  "type" => "standard"),
		],
		
		'calculs' => [],
		'filtres' => [],
	];