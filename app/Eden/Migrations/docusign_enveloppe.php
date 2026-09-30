<?php

return [
		'table_libre' => [
			'nom_table' => "Docusign enveloppe",
			'nom_table_sql' => "docusign_enveloppe",
			'description' => "",
			'feminin' => "",
			'element' => "enveloppe",
			'type_element' => "docusign_enveloppe",
			'element_pluriel' => "enveloppes",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'type_element' => [
                'nom' => "Type élément",
                'type' => 21,
                'obligatoire' => "1",
                'contenu' => '[{"type_element":"devis_vente","valeur":true}]'
            ],
            'element_id' => [
                'nom' => "Élément ID",
                'type' => 22,
                'obligatoire' => "1",
                'contenu' => 'type_element'
            ],
            'statut' => [
                'nom' => "Statut",
                'type' => 20,
				'liste_choix' => 315,
				'lecture_seule' => 1,
				'valeur_defaut' => "0",
            ],
            'docusign_enveloppe_id' => [
				'nom' => 'Id enveloppe docusign',
			],
            'erreur_docusign' => [
                'nom' => "Erreur Docusign",
                'type' => 6,
            ],
            'date_de_signature' => [
                'nom' => "Date de signature",
                'type' => 5
            ]
		],
	];