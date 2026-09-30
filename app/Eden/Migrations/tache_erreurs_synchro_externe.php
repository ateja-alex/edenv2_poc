<?php

return [
    'table_libre' => [
        'nom_table' => 'Erreurs sur les synchronisations de rendez-vous',
        'nom_table_sql' => 'tache_erreurs_synchro_externe',
        'feminin' => 'e',
        'element' => 'tache_erreurs_synchro_externe',
        'type_element' => 'tache_erreurs_synchro_externe',
        'element_pluriel' => 'tache_erreurs_synchro_externe',
        'affichage_recherche' => '#utilisateur#, #modifie_le#',
        'affichage_fiche_type' => '#utilisateur#, #modifie_le#',
        'affichage_dans_liste' => '#utilisateur#, #modifie_le#',
        'affichage_pour_select' => '#utilisateur#, #modifie_le#',
        'non_logue' => 1
    ],
    'champs_libres' => [
        'id_externe' => [
            'nom' => "ID externe",
            'recherche' => 1
        ],
        'utilisateur' => [
            'nom' => 'Utilisateur',
            'type' => 42,
            'type_element_ajax' => 'utilisateur',
            'recherche' => 1
        ],
        'message_erreur' => [
            'nom' => 'Message d\'erreur',
            'type' => 6,
            'recherche' => 1
        ],
        'stacktrace_erreur' => [
            'nom' => 'Stacktrace de l\'erreur',
            'type' => 6,
        ],
        'modele_rdv' => [
            'nom' => 'Modèle du RDV',
            'type' => 6,
        ],
        'synchro_externe' => [
            'nom' => 'Synchronisation externe',
            'type' => 20,
            'liste_choix' => 22,
        ],
    ],
];