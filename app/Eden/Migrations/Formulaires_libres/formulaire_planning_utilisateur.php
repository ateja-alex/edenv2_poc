<?php return [
        'type_element' => 'utilisateur',
    'titre_formulaire' => 'Utilisateur planning',
		        'vue_js' => [
		            'vuejs_data' => "",
		            'vuejs_methods' => "",
		            'surcharger_la_vue' => "1",
                ],
                'champs_libres' => [
		[
			'nom_formulaire' => 'formulaire_planning_utilisateur',
				'type_element' => 'utilisateur',
				'nom_sql' => 'nom',
				'taille_avant' => '0',
				'taille_libelle' => '2',
				'taille_champ' => '4',
				'taille_apres' => '0',
				'ordre' => '0',
                'condition_lecture_seule' => 1
				
			],
			[
			'nom_formulaire' => 'formulaire_planning_utilisateur',
				'type_element' => 'utilisateur',
				'nom_sql' => 'prenom',
				'taille_avant' => '0',
				'taille_libelle' => '2',
				'taille_champ' => '4',
				'taille_apres' => '0',
				'ordre' => '1',
                'condition_lecture_seule' => 1
				
			],
			[
			'nom_formulaire' => 'formulaire_planning_utilisateur',
				'type_element' => 'utilisateur',
				'nom_sql' => 'email',
				'taille_avant' => '0',
				'taille_libelle' => '2',
				'taille_champ' => '4',
				'taille_apres' => '0',
				'ordre' => '4',
                'condition_lecture_seule' => 1
				
			],
			],
	    ];