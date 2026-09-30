<?php

return [
	'type_element' => 'devis_vente',
	'colonnes' => [
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "4", "responsive" => "1", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "6", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", "retour_a_la_ligne_impossible" => "1", ),
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Type", "valeur" => "client_id.type", "ordre" => "3", "type" => "standard", ),
		array("nom" => "Objet", "valeur" => "objet", "ordre" => "5", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "8", "type" => "standard", "responsive" => "1", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "7", "type" => "standard", "responsive" => "1", ),
	],
	'calculs' => [
		array("nom_sql" => "montant_document_ht", "type_calcul" => "SUM", "nom" => "CA HT", "unite" => "€", ),
	],
	'filtres' => [
		array("nom_sql" => "date", ),
		array("nom_sql" => "accepte", "ordre" => "1", ),
		array("nom_sql" => "responsable_commercial_id", "ordre" => "3", ),
		array("nom_sql" => "client_id", "ordre" => "4", ),
		array("nom_sql" => "transforme_en_commande", "ordre" => "5", ),
	],
];