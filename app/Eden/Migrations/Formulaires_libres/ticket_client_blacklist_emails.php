<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "ticket_client_blacklist_emails",
            'type_element' => "ticket_client_blacklist_emails",
            'nom_sql' => "type_blocage",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "2",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => '',

        ],
        [
            'nom_formulaire' => "ticket_client_blacklist_emails",
            'type_element' => "ticket_client_blacklist_emails",
            'nom_sql' => "adresse_email",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'ticket_client_blacklist_emails.type_blocage == 1',

        ],
        [
            'nom_formulaire' => "ticket_client_blacklist_emails",
            'type_element' => "ticket_client_blacklist_emails",
            'nom_sql' => "nom_domaine",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "4",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'ticket_client_blacklist_emails.type_blocage == 2',

        ],
    ],
];
