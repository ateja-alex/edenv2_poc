<?php

return [
    'table_libre' => [
        'nom_table' => "Fichiers de la bibliothèque",
        'nom_table_sql' => "fichier_bibliotheque",
        'description' => "",
        'feminin' => "",
        'element' => "Fichier bibliothèque",
        'type_element' => "fichier_bibliotheque",
        'element_pluriel' => "Fichiers bibliothèque",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'nom_original' => [
            'nom' => "Nom original",
        ],
        'type' => [
            'nom' => "Type",
        ],
        'dimensions' => [
            'nom' => "Dimensions",
        ],
        'poids' => [
            'nom' => "Poids",
        ],
        'chemin' => [
            'nom' => "Chemin",
        ],
        'dossier_parent' => [
            'type_element' => "fichier_bibliotheque",
            'type' => "42",
            'nom' => "Dossier parent",
            'type_element_ajax' => "dossier_bibliotheque",
        ],
        'id_microsoft' => [
            'nom' => "ID Microsoft",
            'nom_sql' => "id_microsoft",
        ],
        'disponible_extranet' => [
            'nom' => 'Disponible pour l\'extranet',
            'type' => 20,
            'liste_choix' => 14,
            'modifier_en_masse' => 1,
        ],
    ],
];