<?php

return [
		'table_libre' => [
			'nom_table' => "Emails factures fournisseurs",
			'nom_table_sql' => "email_facture_fournisseur",
			'description' => "",
			'feminin' => "",
			'element' => "email_facture_fournisseur",
			'type_element' => "email_facture_fournisseur",
			'element_pluriel' => "email_facture_fournisseur",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'sujet' => [
				'nom' => "Sujet",
			],
			'date' => [
				'nom' => "Date",
				'type' => 5,				
			],
			'to' => [
				'nom' => "to",
			],
			'from' => [
				'nom' => "From",
			],
			'bcc' => [
				'nom' => "Bcc",
			],
			'cc' => [
				'nom' => "Cc",
			],
			'pieces_jointes' => [
				'nom' => "Pièces jointes",
			],
			'id_mail' => [
				'nom' => "Id mail",
			],
			'texte' => [
				'nom' => "Texte",
			],
			'traite' => [
				'nom' => "Traité",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
				'modifier_en_masse' => 1,
			],
		],
	];