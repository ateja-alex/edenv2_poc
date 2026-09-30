<?php

return [
		'table_libre' => [
			'nom_table' => "Docusign signataire",
			'nom_table_sql' => "docusign_signataire",
			'description' => "",
			'feminin' => "",
			'element' => "signataire",
			'type_element' => "docusign_signataire",
			'element_pluriel' => "signataires",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'enveloppe_id' => [
                'nom' => "Enveloppe",
                'type' => 42,
                'type_element_ajax' => 'docusign_enveloppe'
            ],
            'email' => [
                'nom' => "Email",
                'format_champ' => "email",
                'obligatoire' => "1",
            ],
            'statut' => [
                'nom' => "Statut",
                'type' => 20,
				'liste_choix' => 315,
            ],
		],
	];