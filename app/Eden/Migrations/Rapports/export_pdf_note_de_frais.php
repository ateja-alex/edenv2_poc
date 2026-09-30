<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'export_pdf_note_de_frais',
	'titre' => 'Export pdf des notes de frais',
	'description' => "Liste des notes de frais pour les exports pdf",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'note_de_frais',

        'colonnes' => [
            ['nom' => 'Client',  'valeur' => 'client_id', 			 'ordre' => 0],
            ['nom' => 'Date',  	'valeur' => 'date', 				 'ordre' => 1],
            ['nom' => 'Montant HT',  	'valeur' => 'montant_ht', 'ordre' => 2],
            ['nom' => 'TVA',  	'valeur' => 'total_tva', 'ordre' => 3],
            ['nom' => 'Montant TTC',  	'valeur' => 'montant_ttc', 'ordre' => 4],
            ['nom' => 'Utilisateur',  	'valeur' => 'utilisateur_id', 'ordre' => 5],

        ],
	

		'filtres' => [],

		
	],
];