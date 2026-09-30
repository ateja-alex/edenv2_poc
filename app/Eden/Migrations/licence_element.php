<?php


return [
    'table_libre' => [
        'nom_table' => "Licence élément",
        'nom_table_sql' => "licence_element",
        'description' => "",
        'feminin' => "",
        'element' => "élément dans la licence",
        'type_element' => "licence_element",
        'element_pluriel' => "éléments dans la licence",
        'fiche' => 0,
        
        'creation_rapide' => 0,
        'disponible_recherche_rapide' => 0,
        'table_systeme' => 1
    ],
    'champs_libres' => [
        'licence_id' => [
            'nom' => "Licence",
            'obligatoire' => 1,
            'type' => 42,
            'type_element_ajax' => 'licence'
        ],
        'type' => [
            'nom' => "Type",
            'type' => 20,
            'liste_choix' => 621
        ],
        'nom' => [
            'nom' => "Nom"
        ],
        'ensemble_id' => [
            'nom' => "Ensemble",
            'type' => 42,
            'type_element_ajax' => 'licence_ensemble'
        ],
        'specifique' => [
            'nom' => 'Spécifique',
            'liste_choix' => 14
        ]
    ],
];