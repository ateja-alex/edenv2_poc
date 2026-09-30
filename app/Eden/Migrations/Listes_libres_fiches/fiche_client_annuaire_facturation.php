<?php

return [
	'type_element' => 'annuaire_facturation',
	'fiche' => 'client',
	'cle_etrangere' => 'client_id',
	'colonnes' => [
		array("nom" => "Active", "valeur" => "active", "ordre" => "0", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Siren", "valeur" => "siren", "ordre" => "2", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Siret", "valeur" => "siret", "ordre" => "3", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Identifiant d'adressage", "valeur" => "adressage_id", "ordre" => "4", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Suffixe d'adressage", "valeur" => "adressage_suffixe", "ordre" => "5", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Adresse", "valeur" => "adresse", "ordre" => "6", "lien_vers_autre_element" => null, "type" => "standard", ),
	],
	'calculs' => [],
	'filtres' => [],
];
