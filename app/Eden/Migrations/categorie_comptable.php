<?php

return [
		'table_libre' => [
			'nom_table' => "Catégories comptables",
			'nom_table_sql' => "categorie_comptable",
			'description' => "",
			'feminin' => "",
			'element' => "catégorie comptable",
			'type_element' => "categorie_comptable",
			'element_pluriel' => "catégories comptables",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
            'affichage_dans_liste' => "#code#",
		],
		'champs_libres' => [
			'code' => [
				'nom' => "Nom",
				'afficher_sur_formulaire' => 1,
                'recherche' => 1
			],
			'code_tva_id' => [
				'nom' => "Code de TVA par défaut",
                'type' => 42,
                'type_element_ajax' => "code_tva",
                'format_champ' => 'select',
				'afficher_sur_formulaire' => 1,
			],
			'zone_fiscale' => [
				'nom' => "Zone fiscale",
				'nom_sql' => "zone_fiscale",
				'type' => "20",
				'liste_choix' => "732",
			],


		],
	];