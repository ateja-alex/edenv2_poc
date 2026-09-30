<?php

return [
		'table_libre' => [
			'nom_table' => "Interventions de maintenance",
			'nom_table_sql' => "maintenance_intervention",
			'description' => "",
			'feminin' => "e",
			'element' => "intervention de maintenance",
			'type_element' => "maintenance_intervention",
			'element_pluriel' => "interventions de maintenances",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#maintenance_id#',
		],
		'champs_libres' => [
			'maintenance_id' => [
				'nom' => "Contrat de maintenance",
				'type' => 42,
				'type_element_ajax' => 'maintenance',
				'obligatoire' => 1,
			],
			'date' => [
				'nom' => "Date",
				'type' => 5,
				'obligatoire' => 1,
			],
			'commentaires' => [
				'nom' => "Commentaires",
				'type' => 6,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 80,
				'valeur_defaut' => 1,
			],
			'facture_vente_id' => [
				'nom' => "Facture",
				'type' => 42,
				'type_element_ajax' => 'facture_vente',
			],
		],
	];