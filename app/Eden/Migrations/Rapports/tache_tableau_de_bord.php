<?php
			return [
			
				"categorie" => "administration",
				"icone" => "table",
				"titre" => "Taches tableau de bord",
				"description" => "Taches à afficher sur le tableau de bord",
				"ordre" => "",
				"inactif" => "0",
				
				"liste_libre" => [
				
                    "id" => 208,
                    "id_rapport" => "tache_tableau_de_bord",
                    'filtres_appliques' => [
                      [
                        'operateur' => 0,
                        'blocs' => [],
                        'filtres' => [
                            [
                                'type_element' => 'tache',
                                'element_id' => null,
                                'champ_liaison' => null,
                                'valeurs' => ["0"],
                                'nom_sql' => 'terminee',
                            ],
                            [
                                'type_element' => 'tache',
                                'element_id' => null,
                                'champ_liaison' => null,
                                'valeurs' => ["0"],
                                'nom_sql' => 'type_tache_rdv',
                            ]
                        ]
                      ],
                    ],
                    "limit" => "",
                    "orderby" => "",
                    "orderby_sens" => "",
                    "avec_inactifs" => "",
                    "bloquer_tri" => "",
                    "type_element" => "tache",
                    "fiche" => "",
                    "cle_etrangere" => "",
                    "formulaire_libre" => "",

                    "colonnes" => [
                        array("nom" => "#", "valeur" => "id", "ordre" => "1", "lien_vers_element" => "", "methode" => "recupere_tache_urgente", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                            array("nom" => "Type", "valeur" => "", "ordre" => "2", "lien_vers_element" => "", "methode" => "icone_type_de_tache", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                            array("nom" => "Client", "valeur" => "<b>#client_id#</b>", "ordre" => "3", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "concatenation", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                            array("nom" => "Titre", "valeur" => "<b>#titre#</b>", "ordre" => "4", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "concatenation", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                            array("nom" => "Echéance", "valeur" => "", "ordre" => "5", "lien_vers_element" => "", "methode" => "format_date_debut", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                            array("nom" => "En charge", "valeur" => "", "ordre" => "7", "lien_vers_element" => "", "methode" => "avatar_utilisateur_en_charge", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "methode", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
                        ],
                    'calculs' => [],
                    'filtres' => [

                        array('nom_sql' => 'terminee'),
                        array('nom_sql' => 'date_de_debut'),

					],
                ],
            ];