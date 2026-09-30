<?php

return [
    'table_libre' => [
        'nom_table' => "Catégories lignes",
        'nom_table_sql' => "categorie_ligne",
        'description' => "",
        'feminin' => "",
        'element' => "Catégorie ligne",
        'type_element' => "categorie_ligne",
        'element_pluriel' => "Catégories projets lignes",
        'fiche' => 0,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'parametre' => 1,
    ],
    'champs_libres' => [
        'type_element' => [
            'nom' => 'Type élement',
            'type' => 21,
            'contenu' => '[{"type_element":"projet","valeur":true}]',
            'afficher_sur_formulaire' => 1,
        ],
        'element_id' => [
            'nom' => "Element ID",
            'type' => 22,
            'contenu' => 'type_element',
            'afficher_sur_formulaire' => 1,
        ],
        'categorie_id' => [
            'nom' => "Catégorie",
            'type' => 42,
            'type_element_ajax' => "categorie_activite",
            'afficher_sur_formulaire' => 1,
        ],
        'vendu' => [
            'nom' => "Vendu",
            'type' => 3,
            'afficher_sur_formulaire' => 1,
        ],
        'previsionnel' => [
            'nom' => "Prévisionnel",
            'type' => 3,
            'afficher_sur_formulaire' => 1,
        ],


    ],
];