<?php

return [
    'table_libre' => [
        'nom_table' => "Url raccourcie",
        'nom_table_sql' => "url_raccourcie",
        'description' => "",
        'feminin' => "",
        'element' => "url_raccourcie",
        'type_element' => "url_raccourcie",
        'element_pluriel' => "urls_raccourcies",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 1,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#lien_origine#',
    ],
    'champs_libres' => [
        'chaine_raccourcie' => [
            'nom' => "Chaîne raccourcie",
            'obligatoire' => 1,
        ],
        'lien_origine' => [
            'nom' => "Lien d'origine",
            'recherche' => 1,
            'obligatoire' => 1,
        ],
    ]
];