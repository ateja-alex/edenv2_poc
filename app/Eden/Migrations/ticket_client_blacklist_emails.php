<?php

return [
    'table_libre' => [
        'nom_table' => "Blacklist des emails",
        'nom_table_sql' => "ticket_client_blacklist_emails",
        'description' => "",
        'feminin' => "",
        'element' => "ticket_client_blacklist_emails",
        'type_element' => "ticket_client_blacklist_emails",
        'element_pluriel' => "ticket_client_blacklist_emails",
        'fiche' => 0,
        'gestion_droits' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 0,
        'module' => 'Gestion commerciale',
        'icone_fontawesome' => 'fa-user-slash',
        'affichage_dans_liste' => '#adresse_email#, #nom_domaine#',

    ],
    'champs_libres' => [
        'adresse_email' => [
            'nom' => "Adresse email",
            'format_champ' => "email",
            'recherche' => 1,
            'unique' => 1,
        ],
        'nom_domaine' => [
            'nom' => "Nom de domaine",
            'recherche' => 1
        ],
        'type_blocage' => [
            'nom' => "Type de blocage",
            'type' => 20,
            'liste_choix' => 21,
        ]
    ]
];
