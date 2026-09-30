<?php

return [
	'type_element' => 'entite',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Entité", "valeur" => "nom", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Facturation électronique active", "valeur" => "facturation_electronique_active", "ordre" => "2", "lien_vers_autre_element" => null, "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];