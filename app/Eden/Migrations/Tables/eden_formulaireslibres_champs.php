<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
    'nom_formulaire' => [

        'type' => 'string',
    ],
    'type_element' => [

        'type' => 'string',
    ],
    'nom_sql' => [

        'type' => 'string',
    ],
    'taille_avant' => [

        'type' => 'integer',
    ],
    
    'taille_libelle' => [

        'type' => 'integer',
    ],
    'taille_champ' => [

        'type' => 'integer',
    ],
    'taille_apres' => [

        'type' => 'integer',
    ],
    'ordre' => [

        'type' => 'integer',
    ],
    'type_champ' => [

        'type' => 'integer',
    ],
   'valeur_html' => [

        'type' => 'longtext',
    ],
	'id_editeur' => [

        'type' => 'integer',
    ],
    'type_vue' => [

        'type' => 'string',
    ],
    'nom_vue' => [

        'type' => 'string',
    ],
    'condition_affichage_v_if' => [

        'type' => 'text',
    ],
    'condition_affichage_en_v_show' => [
        'type' => 'integer',
    ],
    'condition_obligatoire' => [

        'type' => 'text',
    ],
    'condition_lecture_seule' => [

        'type' => 'text',
    ],
    'id_sous_formulaire' => [

        'type' => 'integer',
    ],
    'nom_sous_formulaire' => [

        'type' => 'string',
    ],
    'nom_affichage_sous_formulaire' => [

        'type' => 'string',
    ],
];

