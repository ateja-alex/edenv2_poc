<?php

return [

    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'notification',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["#utilisateur_connecte#"],
	        'nom_sql' => 'utilisateur_id',
        ]]
      ],
	],
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
		array('nom' => 'Notification', 'valeur' => 'contenu_html', 'ordre' => 1),
	],
	'calculs' => [],
	'filtres' => [],
];