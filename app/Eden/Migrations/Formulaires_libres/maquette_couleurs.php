<?php return [

    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "0",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => 'maquette_couleurs',
            'type_element' => 'maquette_couleurs',
            'nom_sql' => 'maquette',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '4',
            'taille_apres' => '6',
            'ordre' => '0',
        ],
        [
            'nom_formulaire' => 'maquette_couleurs',
            'type_element' => 'maquette_couleurs',
            'nom_sql' => 'nom_couleur',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '4',
            'taille_apres' => '0',
            'ordre' => '1',
        ],
        [
            'nom_formulaire' => 'maquette_couleurs',
            'type_element' => 'maquette_couleurs',
            'nom_sql' => 'valeur',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '4',
            'taille_apres' => '0',
            'ordre' => '2',
            'condition_affichage_v_if' => 'maquette_couleurs.nom_couleur !== 11'
        ],
        [
            'nom_formulaire' => 'maquette_couleurs',
            'type_element' => 'maquette_couleurs',
            'nom_sql' => 'valeurs',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '4',
            'taille_apres' => '0',
            'ordre' => '2',
            'condition_affichage_v_if' => 'maquette_couleurs.nom_couleur === 11'
        ],
    ],
];