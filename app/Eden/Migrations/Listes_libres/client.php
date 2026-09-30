<?php

return [
	'type_element' => 'client',
	'desactiver_actions_individuelle' => '[]',
	'afficher_images' => '1',
	'colonnes' => [
		array("nom" => "Client", "valeur" => "raison_sociale", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "tri_par_defaut" => "1", "type" => "standard", "responsive" => "1", ),
		array("nom" => "Téléphone", "valeur" => "telephone", "ordre" => "5", "responsive" => "1", ),
		array("nom" => "Type", "valeur" => "type", "ordre" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial", "ordre" => "6", "type" => "standard", "responsive" => "1", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "8", "type" => "standard", "responsive" => "1", ),
		array("nom" => "Code client", "valeur" => "compte_auxiliaire", "ordre" => "7", "lien_vers_element" => "1", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "type", "ordre" => "1", ),
		array("nom_sql" => "responsable_commercial", "ordre" => "2", ),
		array("nom_sql" => "statut", "ordre" => "3", ),
	],
];