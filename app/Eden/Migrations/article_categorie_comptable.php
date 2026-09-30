<?php

return [
		'table_libre' => [
			'nom_table' => "Comptabilité",
			'nom_table_sql' => "article_categorie_comptable",
			'description' => "",
			'feminin' => "",
			'element' => "catégorie comptable sur article",
			'type_element' => "article_categorie_comptable",
			'element_pluriel' => "catégories comptables sur article",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
            'affichage_dans_liste' => "#categorie_comptable_id#",
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
                'recherche' => 1
			],
			'famille_id' => [
				'nom' => "Famille",
				'type' => 42,
				'type_element_ajax' => 'famille',
			],
			'categorie_comptable_id' => [
				'nom' => "Catégorie comptable",
                'type' => 42,
				'type_element_ajax' => 'categorie_comptable',
                'obligatoire' => 1,
                'recherche' => 1
			],
			'code_tva_id' => [
				'nom' => "Code de TVA ventes",
                'type' => 42,
                'type_element_ajax' => "code_tva",
                'format_champ' => 'select',
				'afficher_sur_formulaire' => 1,
			],
			'code_tva_achat_id' => [
				'nom' => "Code de TVA achats",
                'type' => 42,
                'type_element_ajax' => "code_tva",
                'format_champ' => 'select',
				'afficher_sur_formulaire' => 1,
			],
			'compte_produit' => [
				'nom' => "Compte produit",
                'type' => 42,
                'type_element_ajax' => "compte_comptable",
                'format_champ' => "select",
			],
			'compte_charge' => [
				'nom' => "Compte charge",
                'type' => 42,
                'type_element_ajax' => "compte_comptable",
                'format_champ' => "select",
			],
			'eco_contribution' => [
				'nom' => "Eco-contribution",
				'type' => 20,
				'liste_choix' => 14,
				'valeur_defaut' => '0',
			],
		],
	];