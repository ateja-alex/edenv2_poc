<?php
			return ["id_rapport" => "articles_classique",
	"categorie" => "gestion_commerciale",
	"icone" => "table",
	"titre" => "Articles classiques",
	"description" => "Articles de type classique",
	"ordre" => "1017",
	"type" => "liste_libre",
	"type_rapport" => "liste_libre",
	"type_element" => "article",
	"liste_libre" => [
		"type_element" => "article",
		"id_rapport" => "articles_classique",
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'article',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'type_article',
                ],
            ]
          ],
        ],
		'colonnes' => [
					array("liste_libre_id" => "2", "nom" => "Désignation", "valeur" => "designation", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
						array("liste_libre_id" => "2", "nom" => "Tarif", "valeur" => "tarif", "ordre" => "3", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", "retour_a_la_ligne_impossible" => "1", ),
						array("liste_libre_id" => "2", "nom" => "Famille", "valeur" => "famille_id", "ordre" => "5", "responsive" => "1", ),
						array("liste_libre_id" => "2", "nom" => "Code article", "valeur" => "code_article", "ordre" => "1", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
						array("liste_libre_id" => "2", "nom" => "Disponible pour saisie", "valeur" => "disponible_pour_saisie", "ordre" => "6", "type" => "standard", "responsive" => "1", ),
						],
				'calculs' => [
					],
				'filtres' => [
					array("liste_libre_id" => "2", "nom_sql" => "famille_id", "ordre" => "1", ),
						array("liste_libre_id" => "2", "nom_sql" => "disponible_pour_saisie", "ordre" => "2", ),
						],
				]];