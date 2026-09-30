<?php

return [
		'table_libre' => [
			'nom_table' => "Paiements",
			'nom_table_sql' => "paiement",
			'description' => "",
			'feminin' => "",
			'element' => "paiement",
			'type_element' => "paiement",
			'element_pluriel' => "paiements",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
			'editable_client' => 1,
			'categorie' => 'element_primaire',
			'icone_fontawesome' => 'fa-credit-card',
		],
		'champs_libres' => [
			'mode_paiement_id' => [
				'nom' => "Mode de paiement",
				'type' => 20,
				'liste_choix' => 7,
				'type_element_ajax' => "mode_paiement",
			],
			'compte_bancaire_id' => [
				'nom' => "Compte bancaire",
				'type' => 20,
				'liste_choix' => 4,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'titre' => [
				'nom' => "Titre",
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 1,
                'valeur_defaut' => "#aujourdhui#",
			],
			'montant' => [
				'nom' => "Montant",
				'type' => 3,
			],
			'id_document' => [
				'nom' => "ID document",
                'type' => 22,
				'modification_post_validation' => 1,
                'contenu' => 'type_element',
			],
			'type_element' => [
				'nom' => "Type element",
                'type' => 21,
				'modification_post_validation' => 1,
                'contenu' => '[{"type_element":"devis_vente","valeur":true},{"type_element":"facture_vente","valeur":true},{"type_element":"commande_vente","valeur":true},{"type_element":"acompte_vente","valeur":true},
                {"type_element":"avoir_vente","valeur":true},{"type_element":"acompte_achat","valeur":true},{"type_element":"facture_achat","valeur":true},{"type_element":"avoir_achat","valeur":true},{"type_element":"note_de_frais","valeur":true}]',
			],
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => 'client',
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => 'fournisseur',
			],
			'rapproche' => [
				'nom' => "Rapproché",
				'type' => 20,
				'liste_choix' => 14,
				
			],
			'transaction_id' => [
				'nom' => "ID transaction budget insight",
			],
			'comptabilise' => [
				'nom' => "Comptabilisé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'neutralise' => [
				'nom' => "Neutralisé",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
            'bordereau_id' => [
                'nom' => "Bordereau",
                'type' => 42,
                'type_element_ajax' => 'bordereau',
                'valeur_defaut' => 0,
            ],
			'montant_saisi' => [
				'nom' => "Montant",
				'type' => 3,
				'obligatoire' => 1,
			],
			'type' => [
				'nom' => "Type",
				'type' => 20,
				'liste_choix' => 530,
			],
	],
	];