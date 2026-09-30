<?php

return [
    'table_libre' => [
        'nom_table' => "Jour travaillé",
        'nom_table_sql' => "jour_travaille",
        'description' => "",
        'feminin' => "",
        'element' => "jour travaillé",
        'type_element' => "jour_travaille",
        'element_pluriel' => "jours travaillés",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'date' => [
            'nom' => 'Date',
            'type' => 4,
            'obligatoire' => 1,
        ],
        'utilisateur_id' => [
            'nom' => 'Utilisateur',
            'type' => 42,
            'type_element_ajax' => 'utilisateur',
            'obligatoire' => 1,
        ],
        'matin' => [
            'nom' => 'Matin',
            'type' => 20,
            'liste_choix' => 14,
            'valeur_defaut' => 1
        ],
        'apres_midi' => [
            'nom' => 'Après-midi',
            'type' => 20,
            'liste_choix' => 14,
            'valeur_defaut' => 1
        ],
        'repos' => [
            'nom' => 'Repos',
            'type' => 20,
            'liste_choix' => 14,
            'valeur_defaut' => 1
        ],
        'commentaire' => [
            'nom' => "Commentaire",
            'type' => 6,
        ],
        'ferie' => [
            'nom' => "Férié",
            'type' => 20,
            'liste_choix' => 14,
        ],
        'total_jours' => [
            'nom' => "Total (en jours)",
            'type' => 3,
            'valeur_defaut' => 1
        ],
        'statut' => [
            'nom' => 'Statut',
            'type' => 20,
            'liste_choix' => 701,
        ],
        'date_terminee' => [
            'nom' => 'Date terminée',
            'type' => 4
        ],
        'date_validation' => [
            'nom' => 'Date validation',
            'type' => 4
        ]
    ],
];