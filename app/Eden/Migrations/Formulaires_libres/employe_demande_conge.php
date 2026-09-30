<?php

return [

    'champs_libres' => [

        [

            'type_element' => 'employe_demande_conge',
            'nom_sql' => 'date_de_demande',
            'taille_libelle' => 2,
			'taille_champ' => 4,
            'ordre' => 1,
        ],
        [

            'type_element' => 'employe_demande_conge',
            'nom_sql' => 'employe_id',
            'taille_libelle' => 2,
			'taille_champ' => 4,
            'ordre' => 2,
        ],
        [

			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'date_de_debut',
            'taille_libelle' => 2,
			'taille_champ' => 4,
			'ordre' => 3,
		],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'periode_de_debut',
			'taille_libelle' => 0,
			'taille_champ' => 6,
			'ordre' => 4,
		],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'date_de_fin',
            'taille_libelle' => 2,
			'taille_champ' => 4,
			'ordre' => 5,
		],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'periode_de_fin',
			'taille_libelle' => 0,
			'taille_champ' => 6,
			'ordre' => 6,
		],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'raison',
            'taille_libelle' => 2,
			'taille_champ' => 4,
			'ordre' => 7,
		],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'statut',
            'taille_libelle' => 2,
			'taille_champ' => 4,
            'condition_lecture_seule' => '1',
			'ordre' => 8,
		],
        [
            'type_element' => 'employe_demande_conge',
            'nom_sql' => 'nombre_jours',
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'condition_lecture_seule' => '1',
            'ordre' => 9,
        ],
		[
			'type_element' => 'employe_demande_conge',
			'nom_sql' => 'commentaire',
			'taille_libelle' => 12,
			'taille_champ' => 12,
			'ordre' => 9,
		],
    ],
 

];
