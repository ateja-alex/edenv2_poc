<?php

return [
		'table_libre' => [
			'nom_table' => "Comptes Budget Insight",
			'nom_table_sql' => "budget_insight_comptes",
			'description' => "",
			'feminin' => "",
			'element' => "compte",
			'type_element' => "budget_insight_comptes",
			'element_pluriel' => "comptes",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'coming_balance' => [
				'nom' => "coming_balance",
			],
			'loan' => [
				'nom' => "loan",
			],
			'webid' => [
				'nom' => "webid",
			],
			'number' => [
				'nom' => "number",
			],
			'id' => [
				'nom' => "id",
				'lecture_seule' => 1,
			],
			'bookmarked' => [
				'nom' => "bookmarked",
			],
			'formatted_balance' => [
				'nom' => "formatted_balance",
			],
			'id_connection' => [
				'nom' => "id_connection",
				'lecture_seule' => 1,
			],
			'original_name' => [
				'nom' => "original_name",
				'lecture_seule' => 1,
			],
			'last_update' => [
				'nom' => "last_update",
				'lecture_seule' => 1,
			],
			'usage' => [
				'nom' => "usage",
			],
			'type' => [
				'nom' => "type",
			],
			'deleted' => [
				'nom' => "deleted",
			],
			'id_parent' => [
				'nom' => "id_parent",
			],
			'bic' => [
				'nom' => "bic",
			],
			'iban' => [
				'nom' => "iban",
			],
			'id_type' => [
				'nom' => "id_type",
			],
			'ownership' => [
				'nom' => "ownership",
			],
			'coming' => [
				'nom' => "coming",
			],
			'id_user' => [
				'nom' => "id_user",
			],
			'name' => [
				'nom' => "name",
				'lecture_seule' => 1,
			],
			'balance' => [
				'nom' => "balance",
				'type' => 3,
			],
			'display' => [
				'nom' => "display",
				'lecture_seule' => 1,
			],
			'compte_bancaire_id' => [
				'nom' => "Compte bancaire",
				'type' => 20,
				'liste_choix' => 4,
				'afficher_sur_formulaire' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
                'afficher_sur_formulaire' => 1,
			],
			'encaissements' => [
				'nom' => "Encaissements",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
			],
			'decaissements' => [
				'nom' => "Décaissements",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
			],
			'synchro_id' => [
				'nom' => "Synchro ID",
			],
			'error' => [
				'nom' => "Erreur",
			],
			'state' => [
				'nom' => "Etat",
			],
			'banque' => [
				'nom' => "Banque",
			],
            'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
                'afficher_sur_formulaire' => 1,
			],
		],
	];