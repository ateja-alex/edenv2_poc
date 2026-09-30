<?php

return [

	'type_element' => 'activite',
    'fiche' => 'projet',
    'cle_etrangere' => 'element_id',

	'colonnes' => [

		array('nom' => 'Catégorie', 'valeur' => 'categorie_activite_id', 'ordre' => 0),
		array('nom' => 'Activité', 'valeur' => 'activite', 'ordre' => 1),
		array('nom' => 'Affectations', 'valeur' => 'utilisateurs_id', 'ordre' => 2),
		array('nom' => 'Réalisé', 'valeur' => 'realise', 'ordre' => 3),
		array('nom' => 'Vendu', 'valeur' => 'vendu', 'ordre' => 4),
		array('nom' => 'Prévisionnel', 'valeur' => 'previsionnel', 'ordre' => 5),
        array('nom' => 'Prioritaire', "type" => "champ", "champ" => "prioritaire", 'ordre' => 6),
	],

	'calculs' => [
        array('nom_sql' => 'realise', 'type_calcul' => 'SUM', 'nom' => 'Temps réalisé', 'split' => '', 'unite' => 'H', ),
        array('nom_sql' => 'vendu', 'type_calcul' => 'SUM', 'nom' => 'Temps vendu', 'split' => '', 'unite' => 'H', ),
        array('nom_sql' => 'previsionnel', 'type_calcul' => 'SUM', 'nom' => 'Temps prévisionnel', 'split' => '', 'unite' => 'H', ),
    ],

	'filtres' => [
		array('nom_sql' => 'categorie_activite_id'),
		array('nom_sql' => 'prioritaire'),
		array('nom_sql' => 'utilisateurs_id'),
	],
];