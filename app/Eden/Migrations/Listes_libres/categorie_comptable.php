<?php

return [
	'type_element' => 'categorie_comptable',
	'desactiver_filtres' => '1',
	'desactiver_actions' => '1',
	'desactiver_options' => '1',
	'desactiver_export' => '1',
	'desactiver_recherche' => '1',
	'desactiver_options_individuelle' => '["supprimer","dupliquer"]',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "code", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Zone fiscale", "valeur" => "zone_fiscale", "ordre" => "2", "type" => "standard", ),
		array("nom" => "Modifié le", "valeur" => "modifie_le", "ordre" => "3", "type" => "standard", ),
		array("nom" => "Modifié par", "valeur" => "modifie_par", "ordre" => "4", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];