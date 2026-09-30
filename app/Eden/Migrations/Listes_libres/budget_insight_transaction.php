<?php

return [
	'type_element' => 'budget_insight_transaction',
    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'budget_insight_transaction',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["0"],
	        'nom_sql' => 'reporte_eden',
        ]]
      ],
	],
	'desactiver_options' => '1',
	'desactiver_creation' => '1',
	'desactiver_actions_individuelle' => '[]',
	'colonnes' => [
		array("nom" => "Date", "valeur" => "date", "ordre" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Intitulé", "valeur" => "wording", "ordre" => "1", ),
		array("nom" => "Type", "valeur" => "type", "ordre" => "3", ),
		array("nom" => "Débit", "valeur" => "debit", "ordre" => "4", ),
		array("nom" => "Crédit", "valeur" => "credit", "ordre" => "5", ),
		array("nom" => "Statut", "valeur" => "statut_eden", "ordre" => "7", "type" => "standard", ),
	],
	'calculs' => [
		array("nom_sql" => "credit", "type_calcul" => "SUM", "nom" => "Crédit", "unite" => "€", "ordre" => "1", ),
		array("nom_sql" => "debit", "type_calcul" => "SUM", "nom" => "Débit", "unite" => "€", "ordre" => "2", ),
	],
	'filtres' => [
		array("nom_sql" => "date", ),
		array("nom_sql" => "statut_eden", "ordre" => "2", ),
		array("nom_sql" => "value", "ordre" => "3", ),
		array("nom_sql" => "type", "ordre" => "4", ),
	],
];