<?php

return [
    'desactiver_recherche' => '1',
	'desactiver_options' => '1',
    'desactiver_creation' => '1',
    'colonnes' => [

        array("nom" => "Date", "valeur" => "date", 'ordre' => 0),
        array("nom" => "Eco-organisme", "valeur" => "eco_organisme_id", 'ordre' => 1),
        array("nom" => "Catégorie d'éco-contribution", "valeur" => "categorie_eco_contribution_id", 'ordre' => 2),
        array("nom" => "Quantité unité", "valeur" => "quantite_unite", 'ordre' => 3),
        array("nom" => "Eco-contribution", "valeur" => "eco_contribution", 'ordre' => 4),
        array("nom" => "TVA éco-contribution", "valeur" => "tva", 'ordre' => 5),
    ],
    'calculs' => [],
    'filtres' => [
        array('nom_sql' => 'date'),
    ],
];