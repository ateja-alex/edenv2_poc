<?php

return [
	'type_element' => 'facturation_electronique_achat',
	'colonnes' => [
		array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Facture achat", "valeur" => "facture_achat_id", "ordre" => "2","lien_vers_element" => "1", "lien_vers_autre_element" => 'facture_achat_id', "type" => "standard", ),
		array("nom" => "Avoir achat", "valeur" => "avoir_achat_id", "ordre" => "3","lien_vers_element" => "1", "lien_vers_autre_element" => 'avoir_achat_id', "type" => "standard", ),
		array("nom" => "Émetteur", "valeur" => "nom_emetteur", "ordre" => "4", "type" => "standard", ),
		array("nom" => "SIREN", "valeur" => "siren_emetteur", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Date", "valeur" => "date_document", "ordre" => "6", "type" => "standard", ),
		array("nom" => "Type", "valeur" => "type_code_facturx", "ordre" => "7", "type" => "standard", ),
		array("nom" => "Montant TTC", "valeur" => "montant_ttc", "ordre" => "8", "type" => "standard", ),
		array("nom" => "Fichier", "valeur" => "pdf", "ordre" => "9", "type" => "standard", ),
		array("nom" => "Statut", "valeur" => "statut_facturation_electronique", "ordre" => "10", "type" => "standard", ),
		array("nom" => "Modifié le", "valeur" => "modifie_le", "ordre" => "11", "type" => "standard", ),
	],
	'calculs' => [],
	'filtres' => [
		array("nom_sql" => "siren_emetteur", "ordre" => "1", ),
		array("nom_sql" => "reference_document", "ordre" => "2", ),
		array("nom_sql" => "type_code_facturx", "ordre" => "3", ),
	],
];
