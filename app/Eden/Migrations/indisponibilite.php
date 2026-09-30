<?php

return [
		'table_libre' => [
			'nom_table' => "Indisponibilité",
			'nom_table_sql' => "indisponibilite",
			'description' => "",
			'feminin' => "",
			'element' => "indisponibilité",
			'type_element' => "indisponibilite",
			'element_pluriel' => "indisponibilités",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'type_element' => [
                'nom' => 'Type élément',
                'type' => 21,
                'contenu' => "[{\"type_element\":\"client\",\"valeur\":true},{\"type_element\":\"contact\",\"valeur\":true},{\"type_element\":\"fournisseur\",\"valeur\":true},{\"type_element\":\"lead\",\"valeur\":true},{\"type_element\":\"utilisateur\",\"valeur\":true}]",
                'obligatoire' => 1
            ],
            'element_id' => [
                'nom' => 'Element id',
                'type' => 22,
                'contenu' => 'type_element',
                'obligatoire' => 1
            ],
            'date_debut' => [
                'nom' => 'Date de début',
                'type' => 5,
                'obligatoire' => 1
            ],
            'date_fin' => [
                'nom' => 'Date de fin',
                'type' => 5,
                'obligatoire' => 1
            ],
            'commentaire' => [
                'nom' => 'Commentaire',
                'type' => 6,
                'format_champ' => 'wysiwyg'
            ]
		],
	];