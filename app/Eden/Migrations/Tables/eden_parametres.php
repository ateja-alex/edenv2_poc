<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'id_utilisateur' => [

        'type' => 'integer',
    ],
	'id_entite' => [

        'type' => 'integer',
    ],
    'nom' => [

        'type' => 'string',
    ],
    'valeur' => [

        'type' => 'longtext',
    ],
    'date_creation' => [

        'type' => 'dateTime',
    ],
];

