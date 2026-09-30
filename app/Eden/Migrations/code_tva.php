<?php

return [
		'table_libre' => [
			'nom_table' => "Codes de TVA",
			'nom_table_sql' => "code_tva",
			'description' => "",
			'feminin' => "",
			'element' => "code de TVA",
			'type_element' => "code_tva",
			'element_pluriel' => "codes de TVA",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'code' => [
				'nom' => "Code",
				'afficher_sur_formulaire' => 1,
			],
			'taux' => [
				'nom' => "Taux",
				'type' => 3,
				'afficher_sur_formulaire' => 1,
			],
			'type' => [
				'nom' => "Type",
				'type' => 20,
				'liste_choix' => 112,
				'afficher_sur_formulaire' => 1,
			],
			'sens' => [
				'nom' => "Sens",
				'type' => 20,
				'liste_choix' => 113,
				'afficher_sur_formulaire' => 1,
			],
			'compte_comptable' => [
				'nom' => "Compte comptable",
				'type' => 42,
                'type_element_ajax' => "compte_comptable",
                'format_champ' => "select",
				'afficher_sur_formulaire' => 1,
			],
			'compte_comptable_autoliquidation' => [
				'nom' => "Compte comptable autoliquidation",
				'type' => 42,
                'type_element_ajax' => "compte_comptable",
                'format_champ' => "select",
				'afficher_sur_formulaire' => 1,
			],
            'taux_tva_defaut' => [
                'nom' => "Taux de TVA par defaut en cas d'erreur de reconnaissance",
                'type' => 20,
                'liste_choix' => 14,
                'afficher_sur_formulaire' => 1,
            ],
		],
	];