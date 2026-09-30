<?php

return [
		'table_libre' => [
			'nom_table' => "Jour d'indisponibilité",
			'nom_table_sql' => "jour_indisponibilite",
			'description' => "",
			'feminin' => "",
			'element' => "jour d'indisponibilité",
			'type_element' => "jour_indisponibilite",
			'element_pluriel' => "jours d'indisponibilités",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
            'nom' => [
                'nom' => 'Nom',
                'obligatoire' => 1,
                'recherche' => 1,
			],
			'date_debut' => [
                'nom' => 'Date de début',
                'type' => 4,
                'obligatoire' => 1,
                'doit_etre_plus_petit_que' => "date_fin",
			],
            'date_fin' => [
                'nom' => 'Date de fin',
                'type' => 4,
                'obligatoire' => 1,
			],
            'type_indisponibilite' => [
                'nom' => "Type d'indisponiblité",
                'type' => 20,
                'liste_choix' => 626,
                'obligatoire' => 1,
            ],
		],
	];