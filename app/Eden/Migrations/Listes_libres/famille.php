<?php

return [
	'type_element' => 'famille',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Famille mère", "valeur" => "parent_id", "ordre" => "2", "responsive" => "1", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "parent_id", ),
	],
];