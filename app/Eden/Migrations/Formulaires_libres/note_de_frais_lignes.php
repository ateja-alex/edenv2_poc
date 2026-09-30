<?php return [

    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => 'note_de_frais_lignes',
            'type_element' => 'note_de_frais_lignes',
            'nom_sql' => 'note_de_frais_id',
            'taille_avant' => '0',
            'taille_libelle' => '4',
            'taille_champ' => '8',
            'taille_apres' => '0',
            'ordre' => '0',
            'condition_lecture_seule' => '$root.note_de_frais != undefined && $root.note_de_frais.accepte == 1',
        ],
        [
            'nom_formulaire' => 'note_de_frais_lignes',
            'type_element' => 'note_de_frais_lignes',
            'nom_sql' => 'article_id',
            'taille_avant' => '0',
            'taille_libelle' => '4',
            'taille_champ' => '8',
            'taille_apres' => '0',
            'ordre' => '1',
            'condition_lecture_seule' => '$root.note_de_frais != undefined && $root.note_de_frais.accepte == 1',
        ],
        [
            'nom_formulaire' => 'note_de_frais_lignes',
            'type_element' => 'note_de_frais_lignes',
            'nom_sql' => '',
            'taille_avant' => '0',
            'taille_libelle' => '0',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '6',
            'type_champ' => '2',
            'nom_vue' => 'montants',
        ],
    ],
];