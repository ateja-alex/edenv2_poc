<?php

return [
    'table_libre' => [
        'nom_table' => "Failed jobs",
        'nom_table_sql' => "failed_jobs",
        'description' => "",
        'feminin' => "",
        'element' => "failed_jobs",
        'type_element' => "failed_jobs",
        'element_pluriel' => "failed_jobs",
        'fiche' => 0,
        'parametre' => 1,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'connection' => [
            'nom' => "connection",
        ],
        'queue' => [
            'nom' => "queue",
        ],
        'payload' => [
            'nom' => "payload",
            'type' => 6,
        ],
        'exception' => [
            'nom' => "exception",
            'type' => 6,
        ],
        'failed_at' => [
            'nom' => "failed_at",
            'type' => 18,
        ],
    ],
];