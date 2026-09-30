<?php

return [

    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [

            'type_element' => 'entite',
            'nom_sql' => 'entite_parent',
            'ordre' => 1,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
        ],
        [

			'type_element' => 'entite',
			'nom_sql' => 'nom',
			'ordre' => 2,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'nom_juridique',
			'ordre' => 3,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'facturation_electronique_active',
			'ordre' => 4,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 6,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'adresse_email',
			'ordre' => 5,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'numero_telephone',
			'ordre' => 6,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'siret',
			'ordre' => 7,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'identifiant_adressage',
			'ordre' => 8,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'statut_juridique',
			'ordre' => 9,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 6,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'capital',
			'ordre' => 10,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'numero_tva',
			'ordre' => 11,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'registre_immatriculation',
			'ordre' => 12,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 6,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'adresse',
			'ordre' => 13,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'adresse_complement',
			'ordre' => 14,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'code_postal',
			'ordre' => 15,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'ville',
			'ordre' => 16,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
			'condition_obligatoire' => 'entite.facturation_electronique_active == 1',

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'cgv',
			'ordre' => 17,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
		[
			'type_element' => 'entite',
			'nom_sql' => 'cga',
			'ordre' => 18,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,

		],
        [
            'type_element' => 'entite',
            'nom_sql' => 'logo',
            'ordre' => 19,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 0,

        ],
    ],


];
