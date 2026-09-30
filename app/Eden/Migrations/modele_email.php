<?php

return [
		'table_libre' => [
			'nom_table' => "Modèles d'email",
			'nom_table_sql' => "modele_email",
			'description' => "",
			'feminin' => "",
			'element' => "modèle d'email",
			'type_element' => "modele_email",
			'element_pluriel' => "modèles d'email",
			'fiche' => 1,
			'parametre' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'modele' => [
				'nom' => "Modèle",
				'type' => 6,
				'obligatoire' => 1,
                'format_champ' => 'wysiwyg',
			],
			'sujet_modele' => [
				'nom' => "Sujet modèle",
				'obligatoire' => 1,
			],
			'categorie' => [
				'nom' => "Catégorie",
				'type' => 20,
				'liste_choix' => 36,
			],
            'langue' => [
				'nom' => "Langue",
				'type' => 42,
				'type_element_ajax' => 'traduction_langue',
				'format_champ' => 'select',
				'obligatoire' => true
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'enregistrer_echange' => [
				'nom' => "Enregistrer un échange",
				'type' => 20,
				'liste_choix' => 14,
			],
            'utilisation_template_vide' => [
                'nom' => 'Utilisation du template vierge',
                'type' => 20,
                'liste_choix' => 14
			],
			'type_element_id' => [
				'nom' => "Type élément",
				'type' => 20,
				'liste_choix' => 71,
				'obligatoire' => true
			],
			'condition_affichage' => [
                'nom' => "Condition d'affichage"
            ],
			'regroupement_multiple' => [
				'nom' => "Regroupement par défaut pour les emails multiples",
			],
			'par_defaut' => [
				'nom' => "Par défaut",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
		],
	];