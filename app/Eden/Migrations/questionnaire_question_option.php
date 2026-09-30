<?php

return [
		'table_libre' => [
			'nom_table' => "Questionnaires satisfaction (options)",
			'nom_table_sql' => "questionnaire_question_option",
			'description' => "",
			'feminin' => "",
			'element' => "options de questionnaire",
			'type_element' => "questionnaire_question_option",
			'element_pluriel' => "options de questionnaire",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			
			'question_id' => [
				'nom' => "Question",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'questionnaire_question',
			],
			'option' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			]
		],
	];