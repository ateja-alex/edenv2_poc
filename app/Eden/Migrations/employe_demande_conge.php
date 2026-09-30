<?php

return [
		'table_libre' => [
			'nom_table' => "Demandes de congés",
			'nom_table_sql' => "employe_demande_conge",
			'description' => "",
			'feminin' => "",
			'element' => "demande",
			'type_element' => "employe_demande_conge",
			'element_pluriel' => "demandes",
			'fiche' => 1,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => 'Congé : #employe_id#',
		],
		'champs_libres' => [
			'employe_id' => [
				'nom' => "Employé",
				'type' => 42,
                'type_element_ajax' => 'utilisateur',
                'valeur_defaut' => '#utilisateur_connecte#',
				'recherche' => 1,
                'obligatoire' => 1,
			],
			'date_de_demande' => [
				'nom' => "Date de demande",
				'type' => 5,
				'obligatoire' => 1,
                'valeur_defaut' => '#aujourdhui#',
			],
			'date_de_debut' => [
				'nom' => "Date de début",
				'type' => 4,
				'obligatoire' => 1,
			],
			'date_de_fin' => [
				'nom' => "Date de fin",
				'type' => 4,
				'obligatoire' => 1,
			],
			'periode_de_debut' => [
				'nom' => "Période de début",
				'type' => 20,
				'liste_choix' => 53,
                'obligatoire' => 1,
			],
			'periode_de_fin' => [
				'nom' => "Période de fin",
				'type' => 20,
				'liste_choix' => 53,
                'obligatoire' => 1,
			],
			'raison' => [
				'nom' => "Raison",
				'type' => 20,
				'liste_choix' => 54,
			],
			'commentaire' => [
				'nom' => "Commentaire",
				'type' => 6,
			],
			'statut' => [
				'nom' => "Acceptée",
				'type' => 20,
				'liste_choix' => 3,
				'valeur_defaut' => 0,
			],
			'valide_n1' => [
				'nom' => "Validé par le N+1",
				'type' => 20,
				'liste_choix' => 3,
			],
			'valide_n2' => [
				'nom' => "Validé par le N+2",
				'type' => 20,
				'liste_choix' => 3,
			],
            'nombre_jours' => [
                'nom' => "Nombre de jours",
                'type' => 3,
            ],
		],
	];
