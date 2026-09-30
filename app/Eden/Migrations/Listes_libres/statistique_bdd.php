<?php

return [
	
    'desactiver_filtres' => '1',
	'desactiver_actions' => '1',
	'desactiver_options' => '1',
	'desactiver_export' => '1',
    'desactiver_recherche' => '1',
	'colonnes' => [
		
		array('nom' => '#', 'valeur' => 'id', 'ordre' => 0, "tri_desactive" => "1"),
		array('nom' => 'Table', 'valeur' => 'Table', 'ordre' => 1, "tri_desactive" => "1"),
		array('nom' => 'Nombre Enregistrements', 'valeur' => 'NbEnregistrements', 'ordre' => 2, "tri_desactive" => "1"),
		array('nom' => 'Taille (en Mo)', 'valeur' => 'Taille_Mo', 'ordre' => 3, "tri_desactive" => "1"),
		array('nom' => 'Taille Data (en Mo)', 'valeur' => 'Taille_Data_Mo', 'ordre' => 4, "tri_desactive" => "1"),
		array('nom' => 'Taille Index (en Mo)', 'valeur' => 'Taille_Index_Mo', 'ordre' => 5, "tri_desactive" => "1"),
		array('nom' => 'Pourcentage', 'valeur' => 'Pourcentage', 'ordre' => 6, "tri_desactive" => "1"),
	],
	'calculs' => [],
	'filtres' => [],
];