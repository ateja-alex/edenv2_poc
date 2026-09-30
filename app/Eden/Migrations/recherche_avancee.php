<?php

return [
    'table_libre' => [
        'nom_table' => "Recherche avancée",
        'nom_table_sql' => "recherche_avancee",
        'description' => "",
        'feminin' => "",
        'element' => "recherche avancee",
        'type_element' => "recherche_avancee",
        'element_pluriel' => "recherches avancees",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1,
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom"
        ],
        'type_element' => [
            'nom' =>  'Type élément'
        ],
        'type' => [
            'nom' => "Type",
            'obligatoire' => 1,
        ],
        'id_cible' => [
            'nom' => "Id cible"
        ],
        'utilisateur_id' => [
            'nom' => "Utilisateur",
            'type' => 42,
            'type_element_ajax' => 'utilisateur'
        ],
        'ordre' => [
            'nom' => "Ordre",
            'type' => 2,
        ],
    ],
];