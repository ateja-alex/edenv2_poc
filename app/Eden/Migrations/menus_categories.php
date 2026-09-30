<?php

return [
    'table_libre' => [
        'nom_table' => "Catégories de menus",
        'nom_table_sql' => "menus_categories",
        'description' => "",
        'feminin' => "",
        'element' => "menus_categories",
        'type_element' => "menus_categories",
        'element_pluriel' => "menus_categories",
        'fiche' => 1,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'id_menu_parent' => [
            'nom' => "ID du menu parent",
            'type' => 42,
            'type_element_ajax' => 'menus',
        ],
        'nom' => [
            'nom' => "Nom"
        ],
        'icone' => [
            'nom' => "Icône",
            'format_champ' => 'icone',
        ],
        'ordre' => [
            'nom' => "Ordre",
            'type' => 2,
        ],
        'desactive' => [
            'nom' => "Désactivé",
            'type' => 20,
            'liste_choix' => 14,
        ],
    ],
];