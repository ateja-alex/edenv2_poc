<?php

return [
	'type_element' => 'facture_vente',
	'fiche' => 'client', 
	'cle_etrangere' => 'client_id', 
	"colonnes" => [
					array("nom" => "Référence", "valeur" => "reference_document", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
						array("nom" => "Date", "valeur" => "date", "ordre" => "1", ),
						array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "2", ),
						array("nom" => "TTC", "valeur" => "montant_document_ttc", "ordre" => "3", ),
						array("nom" => "Solde du", "valeur" => "solde_document_ttc", "ordre" => "4", ),
						array("nom" => "Tags", "ordre" => "6", "methode" => "tags_pour_liste", ),
						],
				'calculs' => [
					],
				'filtres' => [
					array("nom_sql" => "date", ),
						],
            ];