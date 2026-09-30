<?php

return [
	'table_libre'   => [
		'nom_table'                   => "email_a_envoyer",
		'nom_table_sql'               => "email_a_envoyer",
		'description'                 => "",
		'feminin'                     => "",
		'element'                     => "email",
		'type_element'                => "email_a_envoyer",
		'element_pluriel'             => "emails",
		'fiche'                       => 0,
		
		'disponible_recherche_rapide' => 0,
		'creation_rapide'             => 0,
		'module'                      => 'Gestion commerciale',
	],
	'champs_libres' => [
		'from_nom' => [
            'nom' => "Nom Expéditeur",
        ],
		'from_email' => [
            'obligatoire' => 1,
        ],
		'to' => [
            'nom' => "Destinataire",
            'obligatoire' => 1,
        ],
		'cc' => [
            'nom' => "Copies",
        ],
		'bcc' => [
            'nom' => "Copies cachées",
        ],
		'sujet' => [
            'nom' => "Sujet",
            'obligatoire' => 1,
        ],
		'contenu' => [
            'nom' => "Contenu",
            'obligatoire' => 1,
        ],
		'pieces_jointes' => [
            'nom' => "Pièces jointes",
        ],
	],
];
