<?php

return [
    'table_libre' => [
        'nom_table' => "Modèles de récurrences",
        'nom_table_sql' => "tache_recurrence_modele",
        'description' => "",
        'feminin' => "e",
        'element' => "tache_recurrence_modele",
        'type_element' => "tache_recurrence_modele",
        'element_pluriel' => "tache_recurrences_modele",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 1,
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
        ],
        'frequence' => [
            'nom' => 'Fréquence',
            'type' => 2,
        ],
        'frequence_jour_concerne' => [
            'nom' => 'Fréquence jour concerné',
            'type' => 2,
        ],
        'type_frequence' => [
            'nom' => 'Type de fréquence',
            'type' => 20,
            'liste_choix' => 10,
            'obligatoire' => 1,
        ],
        'jours_concernes' => [
            'nom' => 'Jours concernés',
            'type' => 10,
            'type_reference' => 20,
            'liste_choix' => 11,
        ],
        'date_de_fin' => [
            'nom' => 'Date de fin',
        ],
    ],
];