<?php

return [
	'type_element' => 'lead',
	'desactiver_options_individuelle' => '["dupliquer"]',
	'desactiver_actions_individuelle' => '[]',
	'formulaire_modale' => '1',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "#prenom# #nom#", "ordre" => "3", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "concatenation", "responsive" => "1", ),
		array("nom" => "Raison sociale", "valeur" => "entreprise", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Créé le", "valeur" => "cree_le", "ordre" => "1", "lien_vers_autre_element" => null, "tri_par_defaut" => "1", "sens_tri_par_defaut" => "1", "type" => "standard", ),
		array("nom" => "Source", "ordre" => "6", "lien_vers_autre_element" => null, "type" => "champ", "champ" => "source", "responsive" => "1", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "7", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Etape", "ordre" => "8", "lien_vers_autre_element" => null, "type" => "champ", "champ" => "etape", "responsive" => "1", ),
		array("nom" => "Notation", "ordre" => "10", "lien_vers_autre_element" => null, "type" => "champ", "champ" => "note", "responsive" => "1", ),
		array("nom" => "Téléphone", "valeur" => "<a href=\"tel:#telephone#\">#telephone#</a>", "ordre" => "5", "lien_vers_autre_element" => null, "type" => "concatenation", "responsive" => "1", ),
		array("nom" => "Adressse email", "valeur" => "adresse_email", "ordre" => "4", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "10", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "nom", "type_calcul" => "COUNT", "nom" => "Par statut", "split" => "statut", "unite" => "leads", ),
		array("nom_sql" => "nom", "type_calcul" => "COUNT", "nom" => "Par étape", "split" => "etape", "unite" => "leads", "ordre" => "1", ),
	],
	'filtres' => [
		array("nom_sql" => "statut", ),
		array("nom_sql" => "etape", "ordre" => "1", ),
		array("nom_sql" => "cree_le", "ordre" => "3", ),
		array("nom_sql" => "responsable_commercial_id", "ordre" => "4", ),
	],
];