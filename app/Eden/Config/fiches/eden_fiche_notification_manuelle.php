<?php

return [
    'modules' => [
        'formulaire_edition_element' => [
            'module' => 'formulaire_edition_element',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],

        'fiche_notification_manuelle_parametrage_destinataire_erp' => [
            'module' => 'fiche_notification_manuelle_parametrage_destinataire_erp',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
            'cacher_bloc_v_if' => 'notification_manuelle.type_notification == 1'
        ],

        'fiche_notification_manuelle_parametrage_destinataire_email' => [
            'module' => 'fiche_notification_manuelle_parametrage_destinataire_email',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
            'cacher_bloc_v_if' => 'notification_manuelle.type_notification == 2'
        ],

        'fiche_notification_manuelle_parametrage_piece_jointe_email' => [
            'module' => 'fiche_notification_manuelle_parametrage_piece_jointe_email',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
            'cacher_bloc_v_if' => 'notification_manuelle.type_notification == 2'
        ],

        'fiche_notification_manuelle_notification_manuelle_element' => [
            'module' => 'fiche_notification_manuelle_notification_manuelle_element',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
    ]
];