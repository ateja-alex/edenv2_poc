<?php 

return [
		'table_libre' => [
			'nom_table' => "Annuaire facturation",
			'nom_table_sql' => "annuaire_facturation",
			'description' => "",
			'feminin' => "",
			'element' => "annuaire facturation",
			'type_element' => "annuaire_facturation",
			'element_pluriel' => "annuaires facturation",
			'fiche' => "0",
			'disponible_recherche_rapide' => "0",
			'creation_rapide' => "0",
			'envoyer_email' => "",
			'module' => "",
			'parametre' => "",
			'affichage_recherche' => "",
			'affichage_dans_liste' => "#nom# - #adressage_id#",
			'template_responsive' => "",
			'editable_client' => "",
			'categorie' => "",
			'icone_fontawesome' => "",
		],
		'champs_libres' => [
			
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => "fournisseur",
			],
			'nom' => [
				'nom' => "Nom",
				'recherche' => 1
			],
			'siren' => [
				'nom' => "Siren",
				'recherche' => 1
			],
			'siret' => [
				'nom' => "Siret",
				'recherche' => 1
			],
			'adressage_id' => [
				'nom' => "Identifiant d'adressage",
			],
			'adressage_suffixe' => [
				'nom' => "Suffixe d'adressage",
			],
            'adresse' => [
				'nom' => "Adresse",
			],
			'active' => [
				'nom' => 'Active',
				'type' => 20,
				'liste_choix' => 14
			]
		],
	];
