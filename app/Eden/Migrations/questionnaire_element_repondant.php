<?php

return [
		'table_libre' => [
			'nom_table' => "Elément répondant aux questionnaires",
			'nom_table_sql' => "questionnaire_element_repondant",
			'description' => "",
			'feminin' => "",
			'element' => "élément répondant aux questionnaires",
			'type_element' => "questionnaire_element_repondant",
			'element_pluriel' => "éléments répondant aux questionnaires",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'type_element' => [
                'nom' => "Type élément",
                'type' => 21,
                'obligatoire' => "1",
				'contenu' => "[{\"type_element\":\"client\",\"valeur\":true},{\"type_element\":\"contact\",\"valeur\":true}]",
            ],
            'element_id' => [
                'nom' => "Élément ID",
                'type' => 22,
                'obligatoire' => "1",
                'contenu' => 'type_element'
            ],
            'type_element_origine' => [
                'nom' => "Type élément origine",
                'type' => 21,
				'contenu' => "[{\"type_element\":\"projet\",\"valeur\":true},{\"type_element\":\"campagne_de_prospection\",\"valeur\":true},{\"type_element\":\"ticket_client\",\"valeur\":true}]",
            ],
            'element_origine_id' => [
                'nom' => "Élément origine ID",
                'type' => 22,
                'contenu' => 'type_element_origine'
            ],
		],
	];