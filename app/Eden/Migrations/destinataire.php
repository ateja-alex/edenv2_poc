<?php 

return [
		'table_libre' => [
			'nom_table' => "Destinataires facturation",
			'nom_table_sql' => "destinataire",
			'description' => "",
			'feminin' => "",
			'element' => "destinataire",
			'type_element' => "destinataire",
			'element_pluriel' => "destinataires",
			'fiche' => "0",
			'disponible_recherche_rapide' => "0",
			'creation_rapide' => "0",
			'envoyer_email' => "",
			'module' => "",
			'parametre' => "",
			'affichage_recherche' => "",
			'affichage_fiche_type' => "",
			'affichage_dans_liste' => "",
			'template_responsive' => "",
			'editable_client' => "",
			'categorie' => "",
			'icone_fontawesome' => "",
		],
		'champs_libres' => [
			
			'adresse_email' => [
				'nom' => "Adresse email",
				'format_champ' => "email",
				'afficher_sur_formulaire' => 1,
			],
			'type' => [
				'nom' => "Type",
				'type' => 20,
				'liste_choix' => 121,
				'afficher_sur_formulaire' => 1,
			],
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
				'afficher_sur_formulaire' => 1,
			],
			'type_de_document' => [
				'nom' => "Type de document",
				'type' => 10,
				'type_reference' => 20,
				'liste_choix' => 71,
				'afficher_sur_formulaire' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];