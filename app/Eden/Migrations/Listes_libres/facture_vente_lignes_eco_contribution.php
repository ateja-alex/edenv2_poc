<?php

return [
    'desactiver_recherche' => '1',
	'desactiver_options' => '1',
    'desactiver_creation' => '1',
    'colonnes' => [

        array("nom" => "Référence document", "valeur" => "reference_document", 'ordre' => 0),
        array("nom" => "Date du document", "valeur" => "date_document", 'ordre' => 1),
        array("nom" => "Article", "valeur" => "article_id", 'ordre' => 2),
        array("nom" => "Quantité", "valeur" => "quantite", 'ordre' => 3),
        array("nom" => "TVA", "valeur" => "tva", 'ordre' => 4),
        array("nom" => "Tarif", "valeur" => "tarif", 'ordre' => 5),
        array("nom" => "Eco-contribution inclue", "valeur" => "eco_contribution_inclue", 'ordre' => 6),
        array("nom" => "Eco-contribution en sus", "valeur" => "eco_contribution_en_sus", 'ordre' => 7),
        array("nom" => "Catégorie d'éco-contribution", "valeur" => "categorie_eco_contribution_id", 'ordre' => 8),
        array("nom" => "Quantité unité", "valeur" => "quantite_unite", 'ordre' => 9),
    ],
    'calculs' => [],
    'filtres' => [
        array('nom_sql' => 'date_document'),
        array('nom_sql' => 'categorie_eco_contribution_id'),
        array('nom_sql' => 'eco_organisme_id'),
        array("nom_sql" => "document_id")
    ],
];