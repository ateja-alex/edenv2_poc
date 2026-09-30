<?php

return [

    'type_element' => 'modele_de_document',
    'titre_formulaire' => 'Facturation électronique',
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "1",
    ],
    'champs_libres' => [

        [
            'type_element' => 'modele_de_document',
            'nom_sql' => 'facturation_electronique_active',
            'ordre' => '1',
            'taille_avant' => '0',
            'taille_libelle' => '4',
            'taille_champ' => '8',
            'taille_apres' => '0',
        ],
        [
            'type_element' => 'modele_de_document',
            'nom_sql' => '',
            'ordre' => '2',
            'taille_avant' => '0',
            'taille_libelle' => '0',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'type_champ' => '2',
            'type_vue' => 'standard',
            'nom_vue' => 'parametrage_facturation_electronique',
            'condition_affichage_v_if' => 'modele_de_document.facturation_electronique_active == 1',
        ],

    ],
];
