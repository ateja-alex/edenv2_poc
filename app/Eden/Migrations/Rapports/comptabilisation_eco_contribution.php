<?php

return [
    "id_rapport" => "comptabilisation_eco_contribution",
	"categorie" => "gestion_commerciale",
	"icone" => "table",
	"titre" => "Comptabilisation Eco-contribution",
	"description" => "Liste des articles avec Eco-contribution",
	"ordre" => 10,
	"type_rapport" => "liste_libre",
	"type_element" => "article_categorie_comptable",
    'liste_libre' => [
        'type_element' => 'article_categorie_comptable',
        "id_rapport" => "comptabilisation_eco_contribution",
        "formulaire_libre" => "comptabilisation_eco_contribution",
        'colonnes' => [
            array('nom' => 'Catégorie comptable', 'valeur' => 'categorie_comptable_id', 'ordre' => 0, 'type' => 'standard'),
            array('nom' => 'Compte Produit', 'valeur' => 'compte_produit', 'ordre' => 1, 'type' => 'standard'),
            array('nom' => 'Compte Charge', 'valeur' => 'compte_charge', 'ordre' => 2, 'type' => 'standard'),
            array('nom' => 'TVA produit', 'valeur' => 'code_tva_id', 'ordre' => 3, 'type' => 'standard'),
            array('nom' => 'TVA charge', 'valeur' => 'code_tva_achat_id', 'ordre' => 4, 'type' => 'standard'),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
        'filtres_appliques' => [
            [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [[
                    'type_element' => 'article_categorie_comptable',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'eco_contribution',
                ]]
            ],
        ]
    ]
];

