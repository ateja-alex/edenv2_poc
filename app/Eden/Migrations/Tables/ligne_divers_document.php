<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
    'document_id' => [

        'type' => 'integer',
        'null' => false,
    ],
    'type_element' => [

        'type' => 'string',
        'null' => false,
    ],
	'contenu' => [

        'type' => 'longtext',
        'null' => true,
    ],
    'ligne' => [

        'type' => 'integer',
        'null' => true,
    ],
    'position' => [

        'type' => 'integer',
        'null' => true,
    ],
    'type' => [

        'type' => 'string',
        'null' => true,
    ],
    'nom' => [

        'type' => 'string',
        'null' => true,
    ],
    'quantite' => [

        'type' => 'integer',
        'null' => true,
    ],
    'tarif' => [

        'type' => 'integer',
        'null' => true,
    ],
    'remise' => [

        'type' => 'integer',
        'null' => true,
    ],
    'tva' => [

        'type' => 'integer',
        'null' => true,
    ],
    'id_style_ligne_document' => [

        'type' => 'integer',
        'null' => true,
    ],
    'type_remise' => [

        'type' => 'integer',
        'null' => true,
    ],
    'type_coefficient' => [

        'type' => 'integer',
        'null' => true,
    ],
    'coefficient' => [

        'type' => 'string',
        'null' => true,
    ],
    'calculateur' => [

        'type' => 'longtext',
        'null' => true,
    ],
    'format' => [

        'type' => 'integer',
        'null' => true,
    ],
    'regroupement_id' => [

        'type' => 'integer',
        'null' => true,
    ],
    'couleur_regroupement' => [

        'type' => 'longtext',
        'null' => true,
    ],
];