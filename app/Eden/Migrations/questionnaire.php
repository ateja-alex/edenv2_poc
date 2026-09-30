<?php

return [
		'table_libre' => [
			'nom_table' => "Questionnaires satisfaction",
			'nom_table_sql' => "questionnaire",
			'description' => "",
			'feminin' => "",
			'element' => "questionnaire",
			'type_element' => "questionnaire",
			'element_pluriel' => "questionnaires",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'parametre' => 1,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'sujet' => [
				'nom' => "Sujet",
			],
			'corps_du_mail' => [
				'nom' => "Corps du mail",
				'format_champ' => "wysiwyg",
				'type' => 6,
			],
			'expediteur' => [
				'nom' => "Expéditeur",
				'type' => 42,
				'type_element_ajax' => 'compte_email',
				'filtres' => [
					'compte_email' => array (
						array (
							'operateur' => 0,
							'exclu' => 0,
							'blocs' => array (),
							'filtres' => 
							array (
								array (
									'type_element' => 'compte_email',
									'champ_liaison' => NULL,
									'valeurs' => array (1),
									'nom_sql' => 'mail_public',
									'operateur' => 0,
								),
							),
						),
					), 
				],
			],
            'reponses_multiples' => [
				'nom' => "Réponses multiples",
				'type' => 20,
				'liste_choix' => 14,
                'format_champ' => 'toggle'
			],
			'type_element_id' => [
				'nom' => "Type élément",
				'type' => 20,
				'liste_choix' => 71
			],
		],
	];