<?php
return [
    'nom_formulaire' => "extranet_ticket_client",
    'titre_formulaire' => "Ticket Client Extranet",
    'type_element' => 'ticket_client',
	'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
        'surcharger_la_vue' => "1",
	],
	'champs_libres' => [
		[
				'nom_formulaire' => "extranet_ticket_client",
				'type_element' => "ticket_client",
				'nom_sql' => "titre",
				'taille_avant' => "5",
				'taille_libelle' => "2",
				'taille_champ' => "5",
				'taille_apres' => "0",
				'ordre' => "1",
				
		],
		[
				'nom_formulaire' => "extranet_ticket_client",
				'type_element' => "ticket_client",
				'nom_sql' => "description",
				'taille_avant' => "5",
				'taille_libelle' => "2",
				'taille_champ' => "5",
				'taille_apres' => "0",
				'ordre' => "2",
				
		],
		[
				'nom_formulaire' => "extranet_ticket_client",
				'type_element' => "ticket_client",
				'nom_sql' => "priorite",
				'taille_avant' => "5",
				'taille_libelle' => "2",
				'taille_champ' => "5",
				'taille_apres' => "0",
				'ordre' => "3",
				
		],
		[
				'nom_formulaire' => "extranet_ticket_client",
				'type_element' => "ticket_client",
				'nom_sql' => "statut",
				'taille_avant' => "5",
				'taille_libelle' => "2",
				'taille_champ' => "5",
				'taille_apres' => "0",
				'ordre' => "4",
				
		],
	],
];