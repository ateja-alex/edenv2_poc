<?php

return [
		'table_libre' => [
			'nom_table' => "Modèle de commentaire",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "modele de commentaire",
			'type_element' => "modele_commentaire",
			'element_pluriel' => "modèles de commentaire",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'afficher_sur_formulaire' => 1,
			],
			'commentaire' => [
				'nom' => "Commentaire",
				'type' => 6,
				'afficher_sur_formulaire' => 1,
				'format_champ' => 'wysiwyg',
			],
		],
	];