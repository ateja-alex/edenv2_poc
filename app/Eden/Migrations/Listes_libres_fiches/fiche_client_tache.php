<?php
			return [
			
				"type_element" => "tache",
				"fiche" => "client",
				"cle_etrangere" => "client_id",
				"formulaire_libre" => "",
                'filtres_appliques' => [
                  [
                    'operateur' => 0,
                    'blocs' => [],
                    'filtres' => [[
                        'type_element' => 'tache',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => ["0"],
                        'nom_sql' => 'type_tache_rdv',
                    ]]
                  ],
                ],
				"colonnes" => [
					array("nom" => "", "valeur" => "", "ordre" => "1", "lien_vers_element" => "0", "methode" => "recupere_tache_urgente", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  ),
						array("nom" => "Type", "valeur" => "", "ordre" => "2", "lien_vers_element" => "0", "methode" => "icone_type_de_tache", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  ),
						array("nom" => "Titre", "valeur" => "<b>#titre#</b><br><span style=\"font-style: italic;\">Début : #date_de_debut# <br>Fin : #date_de_fin#</span>", "ordre" => "3", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "concatenation", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  ),
						array("nom" => "En charge", "valeur" => "", "ordre" => "4", "lien_vers_element" => "0", "methode" => "avatar_utilisateur_en_charge", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "0", "caracteres_max" => "", "standard" => "0", "retour_a_la_ligne_impossible" => "0", "couleur_colonne" => "",  ),
						],
				'calculs' => [
					],
				'filtres' => [

                    array('nom_sql' => 'terminee'),
                    array('nom_sql' => 'date_de_debut'),
					],
				];