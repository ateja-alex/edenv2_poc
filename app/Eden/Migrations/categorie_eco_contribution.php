<?php

return [
    'table_libre' => [
        'nom_table' => "Catégorie d'éco-contribution",
        'nom_table_sql' => "categorie_eco_contribution",
        'description' => "",
        'feminin' => "",
        'element' => "catégorie d'éco-contribution",
        'type_element' => "categorie_eco_contribution",
        'element_pluriel' => "catégories d'éco-contribution",
        'fiche' => 1,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#famille_id#, #nom#, #code#',
    ],
    'champs_libres' => [
        'famille_id' => [
            'nom' => "Famille",
            'type' => 42,
            'type_element_ajax' => 'famille_eco_contribution',
            'obligatoire' => 1,
        ],
        'unite' => [
            'nom' => "Unité pour calcul du tarif",
        ],
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
            'recherche' => 1
        ],
        'code' => [
            'nom' => "Code",
            'obligatoire' => 1,
            'recherche' => 1
        ],
        'exemple' => [
            'nom' => "Exemple",
            'type' => 6
        ],
    ],
];