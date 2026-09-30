<?php

return [
		'table_libre' => [
			'nom_table' => "Synchronisation service élément",
			'nom_table_sql' => "synchronisation_service_element",
			'description' => "",
			'feminin' => "",
			'element' => "synchronisation service élément",
			'type_element' => "synchronisation_service_element",
			'element_pluriel' => "synchronisation service éléments",
			'fiche' => 1,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'affichage_dans_liste' => '#type_element# => #type_externe#',
		],
		'champs_libres' => [
			'synchronisation_service_id' => [
				'nom' => "Synchronisation service",
                'type' => 42,
                'type_element_ajax' => 'synchronisation_service',
			],
            'type_element' => [
				'nom' => "Type element",
			],
			'type_externe' => [
				'nom' => "Type externe"
			],
			'type_synchronisation' => [
				'nom' => "Type de synchronisation",
				'type' => 20,
				'liste_choix' => 727,
				'valeur_defaut' => 0,
				'obligatoire' => 1
			],
			'desactive' => [
				'nom' => "Désactiver la synchronisation",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
			'type_element_destination' => [
				'nom' => "Type élément destination",
			],
			'champ_element_destination' => [
				'nom' => "Champ de destination",
			],
			'delai_lecture' => [
				'nom' => "Délai lecture (heures)",
				'type' => 2,
			],
			'suppression_elements_absents' => [
				'nom' => "Supprimer les éléments absents du flux",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
			'cron_actif' => [
				'nom' => "Cron actif",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
		],
	];