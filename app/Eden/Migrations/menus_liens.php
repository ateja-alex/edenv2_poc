<?php

return [
    'table_libre' => [
        'nom_table' => "Liens de menus",
        'nom_table_sql' => "menus_liens",
        'description' => "",
        'feminin' => "",
        'element' => "menus_liens",
        'type_element' => "menus_liens",
        'element_pluriel' => "menus_liens",
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
        'id_categorie_parent' => [
            'nom' => "ID de la catégorie parente",
            'type' => 42,
            'type_element_ajax' => 'menus_categories',
        ],
        'nom' => [
            'nom' => "Nom"
        ],
        'type_lien' => [
            'nom' => "Type de lien",
            'type' => 20,
            'liste_choix' => 1,
        ],
        'route' => [
            'nom' => "Route"
        ],
        'parametres' => [
            'nom' => "Paramètres pour la route",
            'type' => 10,
            'type_reference' => 0
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