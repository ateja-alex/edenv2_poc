<?php
return [
    'nom_formulaire' => "comptabilisation_eco_contribution",
    'titre_formulaire' => "Comptabilisation Eco-contribution",
    'type_element' => 'article_categorie_comptable',
	'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
        'surcharger_la_vue' => "1",
	],
	'champs_libres' => [
		[
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "categorie_comptable_id",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "2",
				
		],
		[
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "compte_produit",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "3",
				
		],
		[
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "compte_charge",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "4",
				
		],
		[
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "code_tva_id",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "5",
				
		],
        [
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "code_tva_achat_id",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "6",
				
		],
        [
			'nom_formulaire' => "comptabilisation_eco_contribution",
			'type_element' => "article_categorie_comptable",
			'nom_sql' => "eco_contribution",
			'taille_avant' => "0",
			'taille_libelle' => "2",
			'taille_champ' => "4",
			'taille_apres' => "0",
			'ordre' => "7",
				
		],
	],
    'valeurs_par_defaut' => [
		[
			'nom_formulaire' => 'comptabilisation_eco_contribution',
			'nom_sql' => 'eco_contribution',
			'valeur' => '1',	
		],
	],
];