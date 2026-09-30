<?php

return [
		'table_libre' => [
			'nom_table' => "Questionnaires satisfaction (questions)",
			'nom_table_sql' => "questionnaire_question",
			'description' => "",
			'feminin' => "",
			'element' => "Questions du questionnaire",
			'type_element' => "questionnaire_question",
			'element_pluriel' => "Questions du questionnaire",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			
			'question' => [
				'nom' => "Question",
				'obligatoire' => 1,
			],
			
			'type' => [
				'nom' => "Type",
				'obligatoire' => 1,
				'type' => 20,
				'liste_choix' => 46,
			],

            'format' => [
                'nom' => "Format"
            ],

			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],

			'taille' => [
				'nom' => "Taille",
				'type' => 2,
			],
			
			'questionnaire_id' => [
				'nom' => "Questionnaire",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'questionnaire',
			],

            'texte' => [
                'nom' => "Texte",
                'type' => 6,
                'format_champ' => "wysiwyg",
            ],

            'checkbox' => [
                'nom' => "Checkbox",
                'type' => 6,
            ],

            'affichage_conditionnel' => [
                'nom' => "Affichage conditionnel",
                'type' => 6,
            ],

            'obligatoire' => [
                'nom' => "Obligatoire",
                'type' => 20,
                'liste_choix' => 14,
            ],
		],
	];