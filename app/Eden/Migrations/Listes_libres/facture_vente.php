<?php

return [
	'type_element' => 'facture_vente',
	'desactiver_options_individuelle' => '["supprimer"]',
	'desactiver_actions_individuelle' => '[]',
	'colonnes' => [
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "3", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "5", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "retour_a_la_ligne_impossible" => "1", ),
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Objet", "valeur" => "objet", "ordre" => "4", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Echéance", "valeur" => "date_de_reglement", "ordre" => "7", "type" => "standard", ),
		array("nom" => "Statut", "ordre" => "6", "methode" => "tags_pour_liste", "tri_desactive" => "1", "type" => "methode", ),
		array("nom" => "Solde TTC", "valeur" => "solde_document_ttc", "ordre" => "9", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "montant_document_ht", "type_calcul" => "SUM", "nom" => "CA HT", "unite" => "€", ),
	],
	'filtres' => [
		array("nom_sql" => "date", "ordre" => "1", ),
		array("nom_sql" => "regle", "ordre" => "4", ),
		array("nom_sql" => "comptabilise", "ordre" => "3", ),
		array("nom_sql" => "valide", "ordre" => "2", ),
	],
];