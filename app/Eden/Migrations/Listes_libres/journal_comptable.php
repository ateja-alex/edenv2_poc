<?php

return [
	'type_element' => 'journal_comptable',
	'desactiver_filtres' => '1',
	'desactiver_actions' => '1',
	'desactiver_options' => '1',
	'desactiver_export' => '1',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "2", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Code", "valeur" => "code_journal", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "tri_par_defaut" => "1", "type" => "standard", ),
		array("nom" => "Compte contrepartie", "valeur" => "compte_contrepartie", "ordre" => "3", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];