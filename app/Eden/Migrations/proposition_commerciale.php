<?php

return [
		'table_libre' => [
			'nom_table' => "Propositions commerciales",
			'nom_table_sql' => "proposition_commerciale",
			'description' => "",
			'feminin' => "e",
			'element' => "proposition commerciale",
			'type_element' => "proposition_commerciale",
			'element_pluriel' => "propositions commerciales",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#lead_id#, #date#',
		],
		'champs_libres' => [
		
			'lead_id' => [
				'nom' => "Lead",
				'type' => 42,
				'type_element_ajax' => 'lead',
				'afficher_sur_formulaire' => 1,
				'recherche' => 1,
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'afficher_sur_formulaire' => 1,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 96,
				'afficher_sur_formulaire' => 1,
			],
			'reponse' => [
				'nom' => "Réponse",
				'type' => 20,
				'liste_choix' => 97,
				'afficher_sur_formulaire' => 1,
			],
			'references' => [
				'nom' => "Références",
				'type' => 20,
				'liste_choix' => 14,
				'format_champ' => "toggle",
				'afficher_sur_formulaire' => 1,
			],
			
		],
	];