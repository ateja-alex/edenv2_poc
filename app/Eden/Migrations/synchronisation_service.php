<?php

return [
		'table_libre' => [
			'nom_table' => "Synchronisation service",
			'nom_table_sql' => "synchronisation_service",
			'description' => "",
			'feminin' => "",
			'element' => "synchronisation service",
			'type_element' => "synchronisation_service",
			'element_pluriel' => "paramétrages synchronisation service",
			'fiche' => 1,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'service' => [
				'nom' => "Service",
                'type' => 20,
                'liste_choix' => 725,
			],
			'nom' => [
				'nom' => 'Nom'
			],
			'parametres' => [
				'nom' => "Paramamétres de connexion",
				'type' => 6,
			],
			'desactive' => [
				'nom' => "Désactiver la synchronisation",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => 'toggle',
			],
		],
	];