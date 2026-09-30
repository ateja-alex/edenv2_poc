<?php
return array (
    'table_libre' => [
        'nom_table' => 'Participants des tâches',
        'nom_table_sql' => 'tache_participants',
        'feminin' => '',
        'element' => 'tache_participants',
        'type_element' => 'tache_participants',
        'element_pluriel' => 'tache_participants',
        'affichage_recherche' => '#titre# #client_id# ',
        'affichage_fiche_type' => '#titre# #client_id# ',
        'affichage_dans_liste' => '#titre# #client_id# ',
        'affichage_pour_select' => '#titre# #client_id# ',
    ],
    'champs_libres' => [

        'id_tache_organisateur' => [
            'nom' => 'ID de la tâche de l\'organisateur',
            'type' => 42,
            'type_element_ajax' => 'tache',
        ],
        'type_element' => [
            'nom' => 'Type de l\'élement',
            'type' => 21,
            'contenu' => '[{"type_element":"client","valeur":true},{"type_element":"contact","valeur":true},{"type_element":"fournisseur","valeur":true},{"type_element":"utilisateur","valeur":true}]',
        ],
        'element_id' => [
            'nom' => "Element",
            'type' => 22,
            'contenu' => 'type_element',
        ],
        'adresse_email' => [
            'nom' => 'Adresse email',
            'type' => 0,
            'format_champ' => 'email',
            'recherche' => 1,
        ],
        'statut_participant' => [
            'nom' => 'Statut',
            'type' => 20,
            'liste_choix' => 23,
            'valeur_defaut' => 0,
        ],
        'champ_email' => [
            'nom' => 'Nom du champ email utilisé',
        ],
    ]
);