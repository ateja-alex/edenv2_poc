<?php

return [

    'categorie' => 'gestion_commerciale',
    'icone' => 'table',
    'titre' => 'Articles reçus',
    'description' => "",
    'ordre' => 10,
    'inactif' => 0,

    // paramètres de la liste libre
    'liste_libre' => [

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'bl_achat_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'valide',
                ]
            ]
          ],
        ],

        'type_element' => 'bl_achat_lignes',

        'colonnes' => [

            array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
            array('nom' => 'Fournisseur',  'valeur' => 'fournisseur_id_ligne',"lien_vers_autre_element" => "fournisseur_id_ligne", 'ordre' => 2),
            array('nom' => 'Article',  'valeur' => 'article_id',"lien_vers_autre_element" => "article_id", 'ordre' => 3),
            array('nom' => 'Quantité',  'valeur' => 'quantite', 'ordre' => 4),
        ],
        'calculs' => [

        ],
        'filtres' => [

        ],

    ],
];