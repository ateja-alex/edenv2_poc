<?php

return [
		'table_libre' => [
			'nom_table' => "Maintenance",
			'nom_table_sql' => "maintenance",
			'description' => "",
			'feminin' => "e",
			'element' => "maintenance",
			'type_element' => "maintenance",
			'element_pluriel' => "maintenances",
			'fiche' => 1,

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
            'table_systeme' => 1,
            'affichage_dans_liste' => '#client_id#',
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => 'client',
				'obligatoire' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'date_debut' => [
				'nom' => "Date de début",
				'type' => 4,
				'obligatoire' => 1,
			],
			'commentaires' => [
				'nom' => "Commentaires",
				'type' => 6,
			],
			'actif' => [
				'nom' => "Actif",
				'type' => 20,
				'liste_choix' => 14,
				'valeur_defaut' => 1,
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'tarif' => [
				'nom' => "Tarif",
				'type' => 3,
			],
			'prochaine_date_maintenance' => [
				'nom' => "Prochaine date de maintenance",
				'type' => 4,
			],
		],
	];