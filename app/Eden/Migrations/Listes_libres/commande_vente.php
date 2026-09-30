<?php

return [
	'type_element' => 'commande_vente',
	'colonnes' => [
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "3", ),
		array("nom" => "Date", "valeur" => "date", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Objet", "valeur" => "objet", "ordre" => "5", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "6", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Reliquat", "valeur" => "montant_document_reliquat", "ordre" => "7", "type" => "standard", ),
		array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "9", "type" => "standard", ),
		array("nom" => "Date prévisionnelle de livraison", "valeur" => "date_livraison", "ordre" => "9", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "montant_document_ht", "type_calcul" => "SUM", "nom" => "CA HT", "unite" => "€", ),
	],
	'filtres' => [
		array("nom_sql" => "date", ),
		array("nom_sql" => "annule", "ordre" => "1", ),
	],
];