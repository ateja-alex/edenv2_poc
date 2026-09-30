<?php

return [
    'type_element' => 'stocks_tous_les_entrepots',
    'desactiver_creation' => 1,
    'colonnes' => [
        array("nom" => "Article", "valeur" => "article_id", "ordre" => "1"),
        array("nom" => "Famille", "valeur" => "famille_id", "ordre" => "2"),
        array("nom" => "Stock actuel", "valeur" => "stock_actuel", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_actuel", "ordre" => "3"),
        array("nom" => "Stock réservé", "valeur" => "stock_reserve", "ordre" => "4"),
        array("nom" => "Stock disponible", "valeur" => "stock_disponible", "ordre" => "5"),
        array("nom" => "Stock acheté", "valeur" => "stock_achete", "ordre" => "6"),
        array("nom" => "Stock à terme", "valeur" => "stock_a_terme", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_a_terme", "ordre" => "7"),
        array("nom" => "Valorisation du stock actuel", "valeur" => "valorisation_stock_actuel", "ordre" => "8"),
        array("nom" => "Rotation 90j", "valeur" => "rotation_90j", "ordre" => "9"),
        array("nom" => "Rotation 365j", "valeur" => "rotation_365j", "ordre" => "10"),
    ],
    'calculs' => [
    ],
    'filtres' => [
        array("nom_sql" => "famille_id","ordre" => "1"),
        array("nom_sql" => "fournisseur_id","ordre" => "2"),
    ],
];
