<?php

return [
		'table_libre' => [
			'nom_table' => "Paramètrage destinataire email",
			'nom_table_sql' => "parametrage_destinataire_email",
			'description' => "",
			'feminin' => "",
			'element' => "paramètrage destinataire email",
			'type_element' => "parametrage_destinataire_email",
			'element_pluriel' => "paramètrages destinataire email",
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
            'adresse_mail'=>[
                'nom' => 'Adresse mail'
            ],
            'type'=>[
                'nom' => 'Type',
                'type' => 20,
                'liste_choix' => 620,
                'valeur_defaut' => 1
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