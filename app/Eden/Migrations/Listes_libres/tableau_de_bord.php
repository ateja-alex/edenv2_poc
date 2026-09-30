<?php

return [
	'type_element' => 'tableau_de_bord',
	'desactiver_filtres' => '1',
	'desactiver_actions' => '1',
	'desactiver_export' => '1',
	'desactiver_actions_individuelle' => '[]',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Ordre", "ordre" => "2", "lien_vers_autre_element" => null, "tri_par_defaut" => "1", "type" => "champ", "champ" => "ordre", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];