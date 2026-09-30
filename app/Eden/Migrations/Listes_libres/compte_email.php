<?php

return [
	
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
		array('nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 1),
		array('nom' => 'Adresse Email', 'valeur' => 'adresse_email', 'ordre' => 2),
		array('nom' => 'Compte Validé', 'valeur' => 'valide', 'ordre' => 3),
        array('nom' => 'Mail public', 'ordre' => '5', 'tri_desactive' => '1', 'type' => 'champ', 'champ' => 'mail_public'),
        array('nom' => 'Configuration email', 'valeur' => 'configuration_email', 'ordre' => 3),
		array('nom' => 'Par défaut', 'ordre' => '6', 'tri_desactive' => '1', 'type' => 'champ', 'champ' => 'par_defaut'),
    ],
	'calculs' => [],
	'filtres' => [],
];