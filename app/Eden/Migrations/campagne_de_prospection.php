<?php 

return [
		'table_libre' => [
			'nom_table' => "Campagnes de prospection",
			'nom_table_sql' => "campagne_de_prospection",
			'description' => "",
			'feminin' => "e",
			'element' => "campagne de prospection",
			'type_element' => "campagne_de_prospection",
			'element_pluriel' => "campagnes de prospection",
			'fiche' => "1",
			'disponible_recherche_rapide' => "0",
			'creation_rapide' => "0",
			'envoyer_email' => "",
			'module' => "",
			'parametre' => "",
			'affichage_recherche' => "",
			'affichage_fiche_type' => "",
			'affichage_dans_liste' => "#entite_id#, #nom#",
			'template_responsive' => "",
			'editable_client' => "",
			'categorie' => "",
			'icone_fontawesome' => "",
			'synchro_bibliotheque' => "",
			'corbeille' => "",
			'vue_sql' => "0",
			'type_profil_extranet' => "",
			'champ_profil_extranet' => "",
			'acces_extranet' => "",
		],
		'champs_libres' => [
			'entite_id' => [
				'nom' => "Entité",
				'type' => "42",
                'type_element_ajax' => 'entite',
				'obligatoire' => "1",
			],
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => "1",
			],
			'utilisateurs' => [
				'nom' => "Utilisateurs",
                'type' => 10,
                'type_element_ajax' => 'utilisateur',
				'obligatoire' => "1",
			],
			'details' => [
				'nom' => "Détails",
				'type' => "6",
			],
			'date_de_debut' => [
				'nom' => "Date de début",
				'type' => "4",
			],
			'date_de_fin' => [
				'nom' => "Date de fin",
				'type' => "4",
			],
			'budget' => [
				'nom' => "Budget",
				'type' => "3",
			],
			'cout' => [
				'nom' => "Coût",
				'type' => "3",
			],
			'objectif' => [
				'nom' => "Objectif",
				'type' => "3",
			],
			'type' => [
				'nom' => "Type",
				'type' => "1",
			],
			'questionnaire_id' => [
				'nom' => "Questionnaire",
				'type' => 42,
				'type_element_ajax' => 'questionnaire',
			],
		],
	];