<?php

return [
		'table_libre' => [
			'nom_table' => "Paramètrage pièce jointe email",
			'nom_table_sql' => "parametrage_piece_jointe_email",
			'description' => "",
			'feminin' => "",
			'element' => "paramètrage pièce jointe email",
			'type_element' => "parametrage_piece_jointe_email",
			'element_pluriel' => "paramètrages pièce jointe email",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'type_element_id'=>[
                'nom' => 'Type élément',
                'type' => 20,
                'liste_choix' => 71,
            ],
            'notification_manuelle_id' => [
                'nom' => 'Notification manuelle',
                'type' => 42,
                'type_element_ajax' => 'notification_manuelle'
            ],
            'modele_email_id'=>[
                'nom' => 'Modèle email',
                'type' => 42,
                'type_element_ajax' => 'modele_email'
            ],
            'piece_jointe'=>[
                'nom' => 'Pièce jointe',
                'type' => 7
            ],
            'niveau'=>[
                'nom' => 'Niveau',
                'type' => 20,
                'liste_choix' => 622,
                'valeur_defaut' => 2
            ],
            'lien_champ'=>[
                'nom' => 'Lien champ',
                'type' => 6
            ],
            'parametrage_existant'=>[
                'nom' => 'Paramétrage existant',
            ],
            'nom_simplifie'=>[
                'nom' => 'Nom simplifié',
            ],
		],
	];