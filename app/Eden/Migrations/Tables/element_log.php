<?php

return [

    'id_element_log' => [

        'type' => 'integer',
        'cle_primaire' => true
	],
    'type_element' => [

        'type' => 'string',
        'index' => true,
    ],
    'type_action' => [

        'type' => 'integer',
    ],
    'id_element' => [

        'type' => 'integer',
        'index' => true,
    ],
    'id_utilisateur' => [

        'type' => 'integer',
    ],
    'date' => [

        'type' => 'datetime',
    ],
    'description' => [

        'type' => 'text',
    ],
    'details' => [

        'type' => 'longtext',
    ],
    'type_element_modificateur' => [
        'type' => 'string',
        'index' => true,
	],
    'element_id_modificateur' => [

        'type' => 'integer',
        'index' => true,
    ],
	
];

