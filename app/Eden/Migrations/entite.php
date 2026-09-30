<?php

return [
		'table_libre' => [
			'nom_table' => "Entités",
			'nom_table_sql' => "entite",
			'description' => "",
			'feminin' => "e",
			'element' => "entité",
			'type_element' => "entite",
			'element_pluriel' => "entités",
			'fiche' => 1,
			'affichage_recherche' => '#nom#',
	    'affichage_fiche_type' => '#nom#',
	    'affichage_dans_liste' => '#nom#',
	    'affichage_pour_select' => '#nom#',
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
                'recherche' => 1
			],
			'nom_juridique' => [
				'nom' => "Nom juridique",
				'obligatoire' => 1,
			],
			'adresse' => [
				'nom' => "Adresse",
			],
			'adresse_complement' => [
				'nom' => "Adresse complément",
			],
			'code_postal' => [
				'nom' => "Code postal",
			],
			'ville' => [
				'nom' => "Ville",
			],
			'siret' => [
				'nom' => "Siret",
			],
			'statut_juridique' => [
				'nom' => "Statut juridique",
			],
			'capital' => [
				'nom' => "Capital",
			],
			'registre_immatriculation' => [
				'nom' => "Registre immatriculation",
			],
			'numero_tva' => [
				'nom' => "Numéro de TVA",
			],
			'numero_telephone' => [
				'nom' => "Téléphone",
			],
			'adresse_email' => [
				'nom' => "Adresse email",
			],
			'exclure_des_rapports' => [
				'nom' => "Exclure des rapports",
				'type' => 20,
				'liste_choix' => 14,
			],
			'facturation_electronique_active' => [
				'nom' => "Facturation électronique active",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
			'cgv' => [
				'nom' => "CGV",
				'type' => 7,
				'type_fichier' => 'application/pdf',
			],
			'cga' => [
				'nom' => "CGA",
				'type' => 7,
				'type_fichier' => 'application/pdf',
			],
			'couleur' => [
				'nom' => "Couleur",
				'type' => 9,
			],
			'couleur_police' => [
				'nom' => "Couleur police",
				'type' => 9,
			],
            'logo' => [
                'nom' => "Logo",
                'type' => 7,
                'format_champ' => 'logo',
            ],
            'entite_parent' => [
                'nom' => "Entité parent",
                'type' => 42,
                'type_element_ajax' => 'entite'
            ],
			'identifiant_adressage' => [
				'nom' => "Identifiant d'adressage"
			]
		],
	];
