<?php

return [
	'type_element' => 'annuaire_facturation',
	'colonnes' => [
		array("nom" => "Active", "valeur" => "active", "ordre" => "0", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Client", "valeur" => "client_id", "ordre" => "2", "lien_vers_element" => "0", "lien_vers_autre_element" => "client", "type" => "standard", ),
		array("nom" => "Fournisseur", "valeur" => "fournisseur_id", "ordre" => "3", "lien_vers_element" => "0", "lien_vers_autre_element" => "fournisseur", "type" => "standard", ),
		array("nom" => "Siren", "valeur" => "siren", "ordre" => "4", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Siret", "valeur" => "siret", "ordre" => "5", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Identifiant d'adressage", "valeur" => "adressage_id", "ordre" => "6", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Suffixe d'adressage", "valeur" => "adressage_suffixe", "ordre" => "7", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Adresse", "valeur" => "adresse", "ordre" => "8", "lien_vers_autre_element" => null, "type" => "standard", ),
	],
	'calculs' => [],
	'filtres' => [
		array("nom_sql" => "client_id", "ordre" => "1", ),
		array("nom_sql" => "fournisseur_id", "ordre" => "2", ),
	],
];
