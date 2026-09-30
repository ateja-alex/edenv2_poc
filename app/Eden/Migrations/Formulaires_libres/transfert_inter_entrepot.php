<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "transfert_inter_entrepot",
            'type_element' => "transfert_inter_entrepot",
            'nom_sql' => "date",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => "transfert_inter_entrepot",
            'type_element' => "transfert_inter_entrepot",
            'nom_sql' => "entrepot_depart_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => "transfert_inter_entrepot",
            'type_element' => "transfert_inter_entrepot",
            'nom_sql' => "entrepot_arrivee_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => "transfert_inter_entrepot",
            'type_element' => "transfert_inter_entrepot",
            'nom_sql' => "reserve",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => 'transfert_inter_entrepot',
            'type_element' => 'transfert_inter_entrepot',
            'nom_sql' => '',
            'taille_avant' => '',
            'taille_libelle' => '',
            'taille_champ' => '',
            'taille_apres' => '',
            'ordre' => '2',
            'type_champ' => '3',
            'condition_affichage_v_if' => '1',
            'nom_sous_formulaire' => 'transfert_inter_entrepot_sous_formulaire_transfert_inter_entrepot_articles',
            'nom_affichage_sous_formulaire' => 'Article',
        ]
    ],
];
