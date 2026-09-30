<?php 

return [
		'table_libre' => [
			'nom_table' => "Suivi envoi questionnaire",
			'nom_table_sql' => "suivi_envoi_questionnaire",
			'description' => "",
			'feminin' => "",
			'element' => "suivi_envoi_questionnaire",
			'type_element' => "suivi_envoi_questionnaire",
			'element_pluriel' => "suivis_envoi_questionnaire",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => "",
		],
		'champs_libres' => [
            'repondant_id' => [
				'nom' => "Répondant",
				'type' => 42,
				'type_element_ajax' => "questionnaire_element_repondant",
			],
			'id_questionnaire' => [
				'nom' => "ID questionnaire",
				'format_champ' => "",
				'nom_sql' => "id_questionnaire",
				'type' => 2,
				'liste_choix' => "",
			],
			'email_destinataire' => [
				'nom' => "Email destinataire",
				'format_champ' => "email",
				'nom_sql' => "email_destinataire",
				'type' => 0,
				'liste_choix' => "",
			],
			'url_raccourcie_questionnaire' => [
				'nom' => "Url raccourcie du questionnaire",
				'type' => 42,
                'type_element_ajax' => 'url_raccourcie'
			],
			'envoye' => [
				'nom' => "Envoyé",
				'format_champ' => "",
				'nom_sql' => "envoye",
				'type' => 20,
				'liste_choix' => 14,
				'contenu' => "[\"Non\",\"Oui\"]",
			],
			'repondu' => [
				'nom' => "Répondu",
				'format_champ' => "",
				'nom_sql' => "repondu",
				'inactif' => 0,
				'type' => 20,
				'liste_choix' => 14,
				'contenu' => "[\"Non\",\"Oui\"]",
			],
			'date_de_la_reponse' => [
				'nom' => "Date de la réponse",
				'format_champ' => "",
				'nom_sql' => "date_de_la_reponse",
				'type' => 5,
				'liste_choix' => "",
			],
		],
	];