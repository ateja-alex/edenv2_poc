<?php

return [
	'type_element' => 'export_compta_colonne',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "Titre", "valeur" => "titre", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Valeur", "valeur" => "valeur", "ordre" => "3", ),
		array("nom" => "Ordre", "valeur" => "ordre", "ordre" => "4", "lien_vers_autre_element" => null, "tri_par_defaut" => "1", "type" => "standard", ),
		array("nom" => "Modèle", "valeur" => "export_compta_modele_id", "ordre" => "1", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "export_compta_modele_id", ),
	],
];