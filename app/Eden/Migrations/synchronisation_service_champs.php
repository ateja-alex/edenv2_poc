<?php

return [
		'table_libre' => [
			'nom_table' => "Synchronisation service champs",
			'nom_table_sql' => "synchronisation_service_champs",
			'description' => "",
			'feminin' => "",
			'element' => "synchronisation service champ",
			'type_element' => "synchronisation_service_champs",
			'element_pluriel' => "synchronisation service champs",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'synchronisation_service_element_id' => [
				'nom' => "Synchronisation service élément",
                'type' => 42,
                'type_element_ajax' => 'synchronisation_service_element',
			],
            'nom_sql' => [
				'nom' => "Nom sql"
			],
			'nom_externe' => [
				'nom' => "Nom externe"
			],
			'valeur_dur' => [
				'nom' => "Valeur en dur"
			],
			'sens' => [
				'nom' => "Sens",
				'type' => 20,
				'liste_choix' => 726,
				'obligatoire' => 1
			],
			'types_evenements' => [
				'nom' => "Types d'événements",
				'type' => 10,
				'type_reference' => 20,
				'liste_choix' => 728
			],
			'correspondances' => [
				'nom' => "Correspondances",
				'type' => 6
			],
			'cle_mise_a_jour' => [
				'nom' => "Clé de mise à jour",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle'
			],
		],
	];