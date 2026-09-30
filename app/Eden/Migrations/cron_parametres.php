<?php

return [
    'table_libre' => [
        'nom_table' => "Paramètres des Crons",
        'nom_table_sql' => "cron_parametres",
        'description' => "",
        'feminin' => "",
        'element' => "cron_parametre",
        'type_element' => "cron_parametres",
        'element_pluriel' => "cron_parametres",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 0,
        'categorie' => 'element_primaire',
        'icone_fontawesome' => 'fa-repeat',
        'table_systeme' => 1,

    ],
    'champs_libres' => [

        'id_cron' => [

            'nom' => 'ID du Cron',
            'type' => 2,
        ],
        'id_utilisateur' => [

            'nom' => 'ID de l\'utilisateur',
            'type' => 2,
        ],
        'id_entite' => [

            'nom' => 'ID de l\'entité',
            'type' => 2,
        ],
        'nom' => [

            'nom' => 'Nom'
        ],
        'valeur' => [

            'nom' => 'Valeur',
            'type' => 6,
        ],
        'date_creation' => [

            'nom' => 'Date de création',
            'type' => 5,
        ],
    ],
];
