<?php
return [
	'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
	'champs_libres' => [
		[
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "objet",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "10",
				'taille_apres' => "0",
				'ordre' => "1",

		],
		[
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "date",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "6",
				'taille_apres' => "4",
				'ordre' => "2",

		],
        [
            'nom_formulaire' => "bon_retour_achat",
            'type_element' => "bon_retour_achat",
            'nom_sql' => "entrepot_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "6",
            'taille_apres' => "4",
            'ordre' => "3",

        ],
		[
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "commentaires",
				'taille_avant' => "0",
				'taille_libelle' => "12",
				'taille_champ' => "12",
				'taille_apres' => "0",
				'ordre' => "4",

		],
        [
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "devise",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "5",
                'condition_affichage_v_if' => '$root.utilisation_devise_etrangere(true)',

		],
        [
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "taux_de_change",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "6",
                'condition_affichage_v_if' => '$root.utilisation_devise_etrangere()',

		],
        [
				'nom_formulaire' => "bon_retour_achat",
				'type_element' => "bon_retour_achat",
				'nom_sql' => "catalogue_groupement_id",
				'taille_avant' => "0",
				'taille_libelle' => "2",
				'taille_champ' => "4",
				'taille_apres' => "0",
				'ordre' => "8",

		],
	],
];
