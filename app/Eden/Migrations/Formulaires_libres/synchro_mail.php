<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "synchro_mail",
            'type_element' => "synchro_mail",
            'nom_sql' => "utilisateur_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => "synchro_mail",
            'type_element' => "synchro_mail",
            'nom_sql' => "type",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "2",

        ],
        [
            'nom_formulaire' => "synchro_mail",
            'type_element' => "synchro_mail",
            'nom_sql' => "configuration_email",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'condition_affichage_v_if' => "synchro_mail.type == '0' || synchro_mail.type == null"

        ],
        [
            'nom_formulaire' => "synchro_mail",
            'type_element' => "synchro_mail",
            'nom_sql' => "",
            'taille_avant' => "0",
            'taille_libelle' => "0",
            'taille_champ' => "6",
            'taille_apres' => "0",
            'ordre' => "5",
            'type_champ' => 2,
            'nom_vue' => "email_et_boite_mail",
            'type_vue' => "standard",
            'condition_affichage_v_if' => "synchro_mail.type == 1"
        ],
    ],
];