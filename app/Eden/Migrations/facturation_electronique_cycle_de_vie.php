<?php

return [
		'table_libre' => [
			'nom_table' => "Facturation électronique - statuts de cycle de vie",
			'nom_table_sql' => "facturation_electronique_cycle_de_vie",
			'description' => "",
			'feminin' => "",
			'element' => "statut de cycle de vie",
			'type_element' => "facturation_electronique_cycle_de_vie",
			'element_pluriel' => "statuts de cycle de vie",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'affichage_dans_liste' => "#motif#",
		],
		'champs_libres' => [

			'facturation_electronique_achat_id' => [
				'nom' => "Facturation électronique achat",
				'nom_sql' => "facturation_electronique_achat_id",
				'type' => 42,
				'type_element_ajax' => 'facturation_electronique_achat',
			],
			'facture_vente_id' => [
				'nom' => "Facture de vente",
				'nom_sql' => "facture_vente_id",
				'type' => 42,
				'type_element_ajax' => 'facture_vente',
				'filtres' => [
					'facture_vente' => array(
						array(
							'operateur' => 0, 'exclu' => 0, 'blocs' => array(),
							'filtres' => array(
								array(
									'type_element' => 'facture_vente',
									'champ_liaison' => NULL,
									'valeurs' => array(200,201,202,203,204,205,206,207,208,211),
									'nom_sql' => 'statut_facturation_electronique',
									'operateur' => 0,
								),
							),
						),
					),
				],
			],
			'avoir_vente_id' => [
				'nom' => "Avoir de vente",
				'nom_sql' => "avoir_vente_id",
				'type' => 42,
				'type_element_ajax' => 'avoir_vente',
				'filtres' => [
					'avoir_vente' => array(
						array(
							'operateur' => 0, 'exclu' => 0, 'blocs' => array(),
							'filtres' => array(
								array(
									'type_element' => 'avoir_vente',
									'champ_liaison' => NULL,
									'valeurs' => array(200,201,202,203,204,205,206,207,208,211),
									'nom_sql' => 'statut_facturation_electronique',
									'operateur' => 0,
								),
							),
						),
					),
				],
			],
			'statut_achat' => [
				'nom' => "Statut posé (achat)",
				'nom_sql' => "statut_achat",
				'type' => "20",
				'liste_choix' => "729",
			],
			'statut_vente' => [
				'nom' => "Statut posé (vente)",
				'nom_sql' => "statut_vente",
				'type' => "20",
				'liste_choix' => "729",
			],
			'encaissements_par_taux' => [
				'nom' => "Encaissements par taux de TVA",
				'nom_sql' => "encaissements_par_taux",
				'type' => "6",
				'lecture_seule' => 1,
			],
			'date_evenement' => [
				'nom' => "Date de l'évènement",
				'nom_sql' => "date_evenement",
				'type' => "4",
				'valeur_defaut' => "#aujourdhui#",
			],
			'motif_code' => [
				'nom' => "Code motif",
				'nom_sql' => "motif_code",
			],
			'motif' => [
				'nom' => "Motif",
				'nom_sql' => "motif",
				'type' => "6",
			],
			'fichier_cdar' => [
				'nom' => "Fichier CDAR",
				'nom_sql' => "fichier_cdar",
				'type' => "7",
				'lecture_seule' => 1,
			],
			'flow_id' => [
				'nom' => "Flow ID",
				'nom_sql' => "flow_id",
				'lecture_seule' => 1,
			],
			'tracking_id' => [
				'nom' => "Tracking ID",
				'nom_sql' => "tracking_id",
				'lecture_seule' => 1,
			],
			'statut_envoi' => [
				'nom' => "Statut d'envoi",
				'nom_sql' => "statut_envoi",
				'type' => "20",
				'liste_choix' => "736",
				'valeur_defaut' => "1",
			],
			'motif_erreur_envoi' => [
				'nom' => "Motif de l'erreur d'envoi",
				'nom_sql' => "motif_erreur_envoi",
				'type' => "6",
				'lecture_seule' => 1,
			],

		],
	];
