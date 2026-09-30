<?php

return [
    'table_libre' => [
        'nom_table' => "Jobs",
        'nom_table_sql' => "jobs",
        'description' => "",
        'feminin' => "",
        'element' => "jobs",
        'type_element' => "jobs",
        'element_pluriel' => "jobs",
        'fiche' => 0,
        'parametre' => 1,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'queue' => [
            'nom' => "queue",
        ],
        'payload' => [
            'nom' => "payload",
            'type' => 6,
        ],
        'attempts' => [
            'nom' => "attempts",
            'type' => 2,
        ],
        'reserved_at' => [
            'nom' => "reserved_at",
            'type' => 2,
        ],
        'available_at' => [
            'nom' => "available_at",
            'type' => 2,
        ],
        'created_at' => [
            'nom' => "created_at",
            'type' => 2,
        ],
    ],
];