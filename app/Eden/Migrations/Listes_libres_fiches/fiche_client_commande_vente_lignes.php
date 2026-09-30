<?php
			return [
			
				"type_element" => "commande_vente_lignes",
				"fiche" => "client",
				"cle_etrangere" => "client_id_ligne",
				"formulaire_libre" => "",
				
				"colonnes" => [
					array("nom" => "Article", "valeur" => "article_id", "ordre" => "1", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => "article_id", "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
						array("nom" => "Désignation", "valeur" => "designation", "ordre" => "2", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "",  "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
						array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "3", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "",  "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
						array("nom" => "Prix achat", "valeur" => "prix_achat", "ordre" => "4", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "",  "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
						array("nom" => "Date", "valeur" => "document_id.date", "ordre" => "5", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "",  "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
						],
				'calculs' => [
					],
				'filtres' => [
					],
				];