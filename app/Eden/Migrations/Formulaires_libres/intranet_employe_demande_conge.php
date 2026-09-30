<?php
return [
    'type_element' => 'employe_demande_conge',
    'titre_formulaire' => 'Intranet demande de congé',
    'type_formulaire' => 'intranet',
	'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
	'champs_libres' => [
		[
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "date_de_debut",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",

		],
        [
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "periode_de_debut",
            'taille_avant' => "0",
            'taille_libelle' => "0",
            'taille_champ' => "4",
            'taille_apres' => "2",
            'ordre' => "2",

		],
		[
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "date_de_fin",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",

		],
        [
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "periode_de_fin",
            'taille_avant' => "0",
            'taille_libelle' => "0",
            'taille_champ' => "4",
            'taille_apres' => "2",
            'ordre' => "4",

		],
		[
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "raison",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "5",

		],
        [
            'nom_formulaire' => "intranet_employe_demande_conge",
            'type_element' => "employe_demande_conge",
            'nom_sql' => "commentaire",
            'taille_avant' => "0",
            'taille_libelle' => "12",
            'taille_champ' => "12",
            'taille_apres' => "0",
            'ordre' => "6",

		],
	],
];