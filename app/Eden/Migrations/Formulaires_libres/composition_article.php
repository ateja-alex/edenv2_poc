<?php
return [
	'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
	'champs_libres' => [
		[
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "article_id",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "1",
				
		],
		[
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "article_enfant_id",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "2",
				
		],
		[
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "quantite",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "3",
				
		],
		[
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "tarif",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "4",
				
		],
        [
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "prix_achat",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "5",

		],
        [
				'nom_formulaire' => "composition_article",
				'type_element' => "composition_article",
				'nom_sql' => "conditionnement",
                'condition_affichage_v_if' => 'composition_article.article_enfant_id > 0',
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "6",

		],
	],
];