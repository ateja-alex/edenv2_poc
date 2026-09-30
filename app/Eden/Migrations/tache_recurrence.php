<?php

return [
    'table_libre' => [
        'nom_table' => "Récurrences",
        'nom_table_sql' => "tache_recurrence",
        'description' => "",
        'feminin' => "e",
        'element' => "tache_recurrence",
        'type_element' => "tache_recurrence",
        'element_pluriel' => "tache_recurrences",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 1,
    ],
    'champs_libres' => [
        'date_de_debut' => [
            'nom' => "Date de début",
            'type' => 4,
            'obligatoire' => 1,
            'valeur_defaut' => '#aujourdhui#',
        ],
        'date_de_fin' => [
            'nom' => "Date de fin",
            'type' => 4,
        ],
        'frequence' => [

            'nom' => 'Fréquence',
            'type' => 2,
            'valeur_defaut' => 1,
        ],
        'frequence_jour_concerne' => [
            'nom' => 'Fréquence jour concerné',
            'type' => 2,
            'valeur_defaut' => 0,
        ],
        'type_frequence' => [
            'nom' => 'Type de fréquence',
            'type' => 20,
            'liste_choix' => 10,
            'obligatoire' => 1,
            'valeur_defaut' => 1
        ],
        'jours_concernes' => [
            'nom' => 'Jours concernés',
            'type' => 10,
            'type_reference' => 20,
            'liste_choix' => 11,
        ],
        'tache_parent_id' => [
            'nom' => 'ID de la tâche parent',
            'type' => 42,
            'type_element_ajax' => 'tache',
        ],
    ],
];