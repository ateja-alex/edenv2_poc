<?php

return [
		
	'type_element' => 'paiement',
    'fiche' => 'bordereau',
    'cle_etrangere' => 'bordereau_id',

	'colonnes' => [
		
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 0),
		array('nom' => 'Titre', 'valeur' => 'titre', 'ordre' => 1),
		array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 2),
		array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 3),
	],
	
	'calculs' => [],
	
	'filtres' => [
		
		array('nom_sql' => 'date'),
        array('nom_sql' => 'mode_paiement_id'),
	],

    'couleurs' => [
        array(
            "couleur" => "#90ee90",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'paiement',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => "non_vide",
                        'nom_sql' => 'bordereau_id',
                    ],
                ]
              ],
            ],
        ),
    ],

    'orderby' => 'bordereau_id',
    'orderby_sens' => 'DESC',
];