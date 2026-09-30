<?php

return [
    'table_libre' => [
        'nom_table' => "Catalogue groupement",
        'nom_table_sql' => "",
        'description' => "",
        'feminin' => "",
        'element' => "catalogue groupement",
        'type_element' => "catalogue_groupement",
        'element_pluriel' => "catalogues groupements",
        'fiche' => 1,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
        'affichage_recherche' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'archive' => [
            'nom' => "Archivé",
            'type' => 20,
            'liste_choix' => 14
        ],
        'entite_id' => [
            'nom' => 'Entité',
            'type' => 42,
            'type_element_ajax' => 'entite'
        ]
    ],
];