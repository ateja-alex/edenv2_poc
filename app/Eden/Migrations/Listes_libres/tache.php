<?php

return [

    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'tache',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["0"],
	        'nom_sql' => 'type_tache_rdv',
        ]]
      ],
	],
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
		array('nom' => 'Titre', 'valeur' => 'titre', 'ordre' => 2),
		array('nom' => 'Affectée à', 'valeur' => 'affectation', 'ordre' => 3),
		array('nom' => 'Date', 'valeur' => 'date_de_debut', 'ordre' => 4),
		array('nom' => 'Terminée', 'valeur' => 'terminee', 'ordre' => 5),
		
	],
	'calculs' => [],
	'filtres' => [
		
		array('nom_sql' => 'terminee'),
		array('nom_sql' => 'affectation'),
	],
];