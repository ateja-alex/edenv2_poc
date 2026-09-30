<?php

return [

	'colonnes' => [

		array("nom" => "#", "valeur" => "id", "ordre" => "0", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "5", "lien_vers_element" => "1", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "1", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "client_id", "tri_desactive" => "0", "type" => "standard", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
		array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "3", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", 'retour_a_la_ligne_impossible' => 1 ),
		array("nom" => "Tags", "valeur" => "", "ordre" => "6", "lien_vers_element" => "0", "methode" => "tags_pour_liste", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", ),
	],
	'calculs' => [

		array('nom_sql' => 'montant_document_ht', 'type_calcul' => 'SUM', 'nom' => 'CA HT', 'split' => '', 'unite' => '€', ),
	],
	'filtres' => [

		array('nom_sql' => 'date'),
	],
];