<?php

return [

    'desactiver_actions' => "1",
	'desactiver_creation' => "1",
	'colonnes' => [

		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array("nom" => "Import sur mesure", "valeur" => "import_sur_mesure.titre", "ordre" => 1,  "type" => "standard"),
        array("nom" => "Nom fichier", "valeur" => "nom_fichier", "ordre" => 2, ),
		array('nom' => 'Statut', 'valeur' => 'statut', 'ordre' => 3),
		array('nom' => 'Nombre importés', 'valeur' => '#nombres_importes# sur #nombres_a_importer#', 'ordre' => 4, 'type' => 'concatenation'),
        array('nom' => 'Créé par', 'valeur' => 'cree_par', 'ordre' => 5),
	],
	'calculs' => [],
	'filtres' => [],
];