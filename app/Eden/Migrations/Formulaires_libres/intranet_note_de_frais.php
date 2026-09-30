<?php return [
    'type_element' => 'note_de_frais',
    'type_formulaire' => 'intranet',
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "1",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => 'client_id',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '0',

        ],
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => 'projet_id',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '1',

        ],
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => 'date',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '2',

        ],
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => 'commentaire',
            'taille_avant' => '0',
            'taille_libelle' => '12',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '3',

        ],
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => 'cb_entreprise',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '4',
            'taille_apres' => '0',
            'ordre' => '5',

        ],
        [
            'nom_formulaire' => 'intranet_note_de_frais',
            'type_element' => 'note_de_frais',
            'nom_sql' => '',
            'taille_avant' => '0',
            'taille_libelle' => '2',
            'taille_champ' => '12',
            'taille_apres' => '0',
            'ordre' => '4',
            'type_champ' => '2',
            'nom_vue' => 'champ_image_reconaissance_automatique',

        ],
        [
				'nom_formulaire' => 'note_de_frais',
				'type_element' => 'note_de_frais',
				'nom_sql' => '',
				'taille_avant' => '0',
				'taille_libelle' => '2',
				'taille_champ' => '12',
				'taille_apres' => '0',
				'ordre' => '11',
				'type_champ' => '2',
				'nom_vue' => 'devise_etrangere',

		],
    ],
];