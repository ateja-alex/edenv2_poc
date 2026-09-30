<?php

return [
	'type_element' => 'devis_vente',
	'fiche' => 'client', 
	'cle_etrangere' => 'client_id', 
	"colonnes" => [
					array("nom" => "Référence", "valeur" => "reference_document", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
						array("nom" => "Date", "valeur" => "date", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
						array("nom" => "HT", "valeur" => "montant_document_ht", "ordre" => "5", ),
						array("nom" => "Objet", "valeur" => "objet", "ordre" => "2", "lien_vers_element" => "1", "type" => "standard", ),
						array("nom" => "Responsable commercial", "valeur" => "responsable_commercial_id", "ordre" => "7", "type" => "standard", ),
						array("nom" => "Statut", "valeur" => "statut", "ordre" => "6", "type" => "standard", ),
						],
				'calculs' => [
					],
				'filtres' => [
					array("nom_sql" => "date", ),
						],
            ];