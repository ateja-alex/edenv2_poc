<?php

return [
	'type_element' => 'avoir_vente',
	'colonnes' => [
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "3", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "5", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "retour_a_la_ligne_impossible" => "1", ),
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Objet", "valeur" => "objet", "ordre" => "4", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "6", "type" => "standard", ),
		array("nom" => "Statut", "ordre" => "7", "methode" => "tags_pour_liste", "tri_desactive" => "1", "type" => "methode", ),
	],
	'calculs' => [
		array("nom_sql" => "montant_document_ht", "type_calcul" => "SUM", "nom" => "CA HT", "unite" => "€", ),
	],
	'filtres' => [
		array("nom_sql" => "date", ),
	],
];