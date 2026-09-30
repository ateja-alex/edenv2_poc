<?php
return [
    'id_rapport' => '',
    'limit' => "0",
    'orderby' => "",
    'orderby_sens' => "",
    'avec_inactifs' => "0",
    'bloquer_tri' => "0",
    'colonnes' => [
        array("nom" => "#", "valeur" => "id", "ordre" => "0"),
        array("nom" => "Type de blocage", "valeur" => "type_blocage", "ordre" => "1"),
        array("nom" => "Adresse email", "valeur" => "adresse_email", "ordre" => "2"),
        array("nom" => "Nom de domaine", "valeur" => "nom_domaine", "ordre" => "3"),
    ],
    'calculs' => [],
    'filtres' => [

        array('nom_sql' => 'adresse_email'),
        array('nom_sql' => 'nom_domaine'),
    ],
];