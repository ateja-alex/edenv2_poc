<?php

return [
	'type_element' => 'maquette',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Nom de l'application", "valeur" => "nom_application", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Maquette par défaut", "valeur" => "par_defaut", "ordre" => "2", "lien_vers_autre_element" => null, "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "nom_application", ),
	],
];