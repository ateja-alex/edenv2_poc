<?php


return [
    'table_libre' => [
        'nom_table' => "Licence ensemble élément",
        'nom_table_sql' => "licence_ensemble_element",
        'description' => "",
        'feminin' => "",
        'element' => "élément dans l'ensemble pour les licence",
        'type_element' => "licence_ensemble_element",
        'element_pluriel' => "éléments dans l'ensemble pour les licence",
        'fiche' => 0,
        
        'table_systeme' => 1,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0
    ],
    'champs_libres' => [
        'licence_ensemble_id' => [
            'nom' => "Licence ensemble",
            'obligatoire' => 1,
            'type' => 42,
            'type_element_ajax' => 'licence_ensemble'
        ],
        'type' => [
            'nom' => "Type",
            'type' => 20,
            'liste_choix' => 621
        ],
        'nom' => [
            'nom' => "Nom"
        ],
        'specifique' => [
            'nom' => 'Spécifique',
            'liste_choix' => 14
        ]
    ],
];