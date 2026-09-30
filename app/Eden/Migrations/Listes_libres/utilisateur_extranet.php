<?php

return [
	'type_element' => 'utilisateur',
	'desactiver_actions_individuelle' => '[]',
	'colonnes' => [
		array("nom" => "Email", "valeur" => "email", "ordre" => "1", "type" => "standard"),
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "2", "type" => "standard"),
		array("nom" => "Prénom", "valeur" => "prenom", "ordre" => "3", "type" => "standard"),
		array("nom" => "Profil", "valeur" => "profil", "ordre" => "4", "type" => "standard"),
        array("nom" => "Langue", "valeur" => "langue", "ordre" => "5", "type" => "standard"),
        array("nom" => "Autorisé à se connecter", "valeur" => "autorise_a_se_connecter", "ordre" => "6", "type" => "standard"),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "profil", "ordre" => "1", ),
		array("nom_sql" => "autorise_a_se_connecter", "ordre" => "2", ),
	],
];