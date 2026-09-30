<?php

return [
    'categorie' => 'gestion_commerciale',
    'icone' => 'table',
    'titre' => 'Détails ligne stocks pour tous les entrepôts',
    'description' => "Liste affichée pour le détail des listes",
    'ordre' => 100,
    'inactif' => 0,

    // paramètres de la liste libre
    'liste_libre' => [
        'type_element' => 'stocks_tous_les_entrepots_par_conditionnement',
        'desactiver_options' =>1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array("nom" => "Conditionnement", "valeur" => "conditionnement_id", "ordre" => "0"),
            array("nom" => "Stock actuel", "valeur" => "stock_actuel", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_actuel", "ordre" => "2"),
            array("nom" => "Stock réservé", "valeur" => "stock_reserve", "methode" => "affichage_quantite", "arguments" => "stock_reserve", "ordre" => "3"),
            array("nom" => "Stock disponible", "valeur" => "stock_disponible", "methode" => "affichage_quantite", "arguments" => "stock_disponible", "ordre" => "4"),
            array("nom" => "Stock acheté", "valeur" => "stock_achete", "methode" => "affichage_quantite", "arguments" => "stock_achete", "ordre" => "5"),
            array("nom" => "Stock à terme", "valeur" => "stock_a_terme", "methode" => "affichage_colonne_avec_alerte", "arguments" => "stock_a_terme", "ordre" => "6"),
            array("nom" => "Valorisation du stock actuel", "valeur" => "valorisation_stock_actuel", "methode" => "affichage_quantite", "arguments" => "valorisation_stock_actuel", "ordre" => "7"),
            array("nom" => "Rotation 90j", "valeur" => "rotation_90j", "methode" => "affichage_quantite", "arguments" => "rotation_90j", "ordre" => "8"),
            array("nom" => "Rotation 365j", "valeur" => "rotation_365j", "methode" => "affichage_quantite", "arguments" => "rotation_365j", "ordre" => "9"),
            array("nom" => "Seuil mini", "valeur" => "seuil_mini", "methode" => "affichage_quantite", "arguments" => "seuil_mini", "ordre" => "10"),
            array("nom" => "Seuil alerte", "valeur" => "seuil_alerte", "methode" => "affichage_quantite", "arguments" => "seuil_alerte", "ordre" => "11"),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];
