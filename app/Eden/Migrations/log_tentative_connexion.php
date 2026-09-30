<?php

return [
    'table_libre' => [
        'nom_table' => "Logs tentatives de connexion",
        'nom_table_sql' => "log_tentative_connexion",
        'description' => "",
        'feminin' => "",
        'element' => "log tentatives de connexion",
        'type_element' => "log_tentative_connexion",
        'element_pluriel' => "logs tentatives de connexions",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'module' => 'ERP',
        'table_systeme' => 1,
    ],
    'champs_libres' => [
        'date' => [
            'nom' => "Date",
            'type' => 5,
        ],
        'adresse_email' => [
            'nom' => "Adresse email",
        ],
        'ip' => [
            'nom' => "Adresse IP",
        ],
        'url' => [
            'nom' => "URL",
        ],
        'navigateur' => [
            'nom' => "Navigateur",
        ],
        'support' => [
            'nom' => "Support",
        ],
        'emplacement' => [
            'nom' => "Emplacement",
        ],
        'succes' => [
            'nom' => "Succès",
        ],
        'source' => [
            'nom' => "Source",
            'type' => 20,
            'liste_choix' => 561,
        ]
    ],
];