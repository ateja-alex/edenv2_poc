<?php

return [
		'table_libre' => [
			'nom_table' => "Questionnaires satisfaction (réponses)",
			'nom_table_sql' => "questionnaire_reponse",
			'description' => "",
			'feminin' => "",
			'element' => "réponse de questionnaire",
			'type_element' => "questionnaire_reponse",
			'element_pluriel' => "réponses de questionnaire",
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
            'repondant_id' => [
				'nom' => "Répondant",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'questionnaire_element_repondant',
			],
            'groupe_reponses_id' => [
				'nom' => "Groupe de réponses",
				'obligatoire' => 1,
			],
			'reponse' => [
				'nom' => "Réponse",
			],
		],
	];