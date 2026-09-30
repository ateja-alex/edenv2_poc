<?php

return [
		'table_libre' => [
			'nom_table' => "Facturation électronique achat",
			'nom_table_sql' => "facturation_electronique_achat",
			'description' => "",
			'feminin' => "",
			'element' => "facturation électronique achat",
			'type_element' => "facturation_electronique_achat",
			'element_pluriel' => "facturations électroniques achat",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'affichage_dans_liste' => "#reference_document# - #nom_emetteur#",
		],
		'champs_libres' => [

			'flow_id' => [
				'nom' => "Flow ID",
				'nom_sql' => "flow_id",
				'lecture_seule' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
				'nom_sql' => "entite_id",
				'type' => 42,
				'type_element_ajax' => 'entite',
				'lecture_seule' => 1,
			],
			'siren_destinataire' => [
				'nom' => "SIREN destinataire",
				'nom_sql' => "siren_destinataire",
				'lecture_seule' => 1,
			],
			'statut_facturation_electronique' => [
				'nom' => "Statut",
				'nom_sql' => "statut_facturation_electronique",
				'type' => "20",
				'liste_choix' => "729",
				'lecture_seule' => 1,
			],
			'tracking_id' => [
				'nom' => "Tracking ID",
				'nom_sql' => "tracking_id",
				'lecture_seule' => 1,
			],
			'flow_type' => [
				'nom' => "Type de flow",
				'nom_sql' => "flow_type",
				'lecture_seule' => 1,
			],
			'siren_emetteur' => [
				'nom' => "SIREN émetteur",
				'nom_sql' => "siren_emetteur",
				'recherche' => 1,
				'lecture_seule' => 1,
			],
			'nom_emetteur' => [
				'nom' => "Nom émetteur",
				'nom_sql' => "nom_emetteur",
				'recherche' => 1,
				'lecture_seule' => 1,
			],
			'reference_document' => [
				'nom' => "Référence du document",
				'nom_sql' => "reference_document",
				'recherche' => 1,
				'lecture_seule' => 1,
			],
			'date_document' => [
				'nom' => "Date du document",
				'nom_sql' => "date_document",
				'type' => "4",
				'lecture_seule' => 1,
			],
			'type_code_facturx' => [
				'nom' => "Type de document (BT-3)",
				'nom_sql' => "type_code_facturx",
				'type' => "20",
				'liste_choix' => "730",
				'lecture_seule' => 1,
			],
			'montant_ht' => [
				'nom' => "Montant HT",
				'nom_sql' => "montant_ht",
				'type' => "3",
				'lecture_seule' => 1,
			],
			'montant_tva' => [
				'nom' => "Montant TVA",
				'nom_sql' => "montant_tva",
				'type' => "3",
				'lecture_seule' => 1,
			],
			'montant_ttc' => [
				'nom' => "Montant TTC",
				'nom_sql' => "montant_ttc",
				'type' => "3",
				'lecture_seule' => 1,
			],
			'pdf' => [
				'nom' => "PDF Factur-X",
				'nom_sql' => "pdf",
				'type' => "7",
				'lecture_seule' => 1,
			],
			'facture_achat_id' => [
				'nom' => "Facture d'achat",
				'nom_sql' => "facture_achat_id",
				'type' => 42,
				'type_element_ajax' => 'facture_achat',
			],
			'avoir_achat_id' => [
				'nom' => "Avoir d'achat",
				'nom_sql' => "avoir_achat_id",
				'type' => 42,
				'type_element_ajax' => 'avoir_achat',
			],

		],
	];
