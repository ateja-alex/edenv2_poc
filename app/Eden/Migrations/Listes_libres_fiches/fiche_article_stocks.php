<?php

return [

    'type_element' => 'stocks',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',

    'colonnes' => [
        array("nom" => "Entrepôt", "valeur" => "entrepot_id", "ordre" => "1"),
        array("nom" => "Stock actuel", "valeur" => "stock_actuel", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_actuel", "ordre" => "3"),
        array("nom" => "Stock réservé", "valeur" => "stock_reserve", "ordre" => "4"),
        array("nom" => "Stock disponible", "valeur" => "stock_disponible", "ordre" => "5"),
        array("nom" => "Stock acheté", "valeur" => "stock_achete", "ordre" => "6"),
        array("nom" => "Stock à terme", "valeur" => "stock_a_terme", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_a_terme", "ordre" => "7"),
        array("nom" => "Valorisation du stock actuel", "valeur" => "valorisation_stock_actuel", "ordre" => "8"),
    ],

    'calculs' => [
        array("nom_sql" => "rotation_90j","type_calcul" => "SUM", "nom" => "Rotation ( sur 90 J)","ordre" => "1"),
        array("nom_sql" => "rotation_365j", "type_calcul" => "SUM", "nom" => "Rotation ( sur 365 J)", "ordre" => "2"),
    ],

    'filtres' => [
        array("nom_sql" => "fournisseur_id"),
    ],
];