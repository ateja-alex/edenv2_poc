<?php

return [
    'table_libre' => [
        'nom_table' => 'Crons',
        'nom_table_sql' => 'cron',
        'description' => '',
        'feminin' => '',
        'element' => 'cron',
        'type_element' => 'cron',
        'element_pluriel' => 'crons',
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 0,
        'categorie' => 'element_primaire',
        'icone_fontawesome' => 'fa-repeat',
        'table_systeme' => 1,

    ],
    'champs_libres' => [

        'nom' => [

            'nom' => 'Nom',
            'recherche' => "1",
        ],
        'en_cours' => [

            'nom' => 'En cours d\'exécution',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => 'toggle',
        ],
        'derniere_execution' => [

            'nom' => 'Date de dernière exécution',
            'type' => 5,
        ],
        'temps_avant_relance' => [

            'nom' => 'Temps avant relance (en heures)',
            'type' => 2,
        ],
        'standard' => [

            'nom' => 'Standard',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => 'toggle',
        ]
    ],
];
