<?php

return [
    'table_libre' => [
        'nom_table' => "Catalogue tarif",
        'nom_table_sql' => "",
        'description' => "",
        'feminin' => "",
        'element' => "catalogue tarif",
        'type_element' => "catalogue_tarif",
        'element_pluriel' => "catalogues tarifs",
        'fiche' => 1,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'catalogue_groupement_id' => [
            'nom' => "Catalogue groupement",
            'type' => 42,
            'type_element_ajax' => 'catalogue_groupement',
        ],
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'date_debut' => [
            'nom' => 'Date de début',
            'type' => 4,
            'obligatoire' => 1,
        ],
        'date_fin' => [
            'nom' => 'Date de fin',
            'type' => 4
        ]
    ],
];