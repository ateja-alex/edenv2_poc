<?php


return [
    'table_libre' => [
        'nom_table' => "Licence ensemble",
        'nom_table_sql' => "licence_ensemble",
        'description' => "",
        'feminin' => "",
        'element' => "ensemble pour les licences",
        'type_element' => "licence_ensemble",
        'element_pluriel' => "ensembles pour les licences",
        'fiche' => 0,

        'creation_rapide' => 0,
        'disponible_recherche_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'recherche' => 1,
            'obligatoire' => 1,
        ],
        'specifique' => [
            'nom' => 'Spécifique',
            'liste_choix' => 14
        ]
    ],
];