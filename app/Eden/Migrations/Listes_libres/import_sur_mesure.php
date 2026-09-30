<?php

return [

    'desactiver_actions' => "1",
	'desactiver_options' => "1",
	'desactiver_creation' => "1",
	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array("nom" => "Titre", "valeur" => "titre", "ordre" => 1, ),
		array('nom' => 'Type élément', 'valeur' => 'type_element', 'ordre' => 2),
		array('nom' => 'Tables jointes', 'valeur' => 'tables_jointes', 'ordre' => 3),
		array('nom' => 'Créé par', 'valeur' => 'cree_par', 'ordre' => 4),
	],
	'calculs' => [],
	'filtres' => [],
    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'import_sur_mesure',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["1"],
	        'nom_sql' => 'complet',
        ]]
      ],
	]
];