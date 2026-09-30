<?php
return [

    'type_element' => 'article_fournisseur',
    'fiche' => 'fournisseur',
    'cle_etrangere' => 'fournisseur_id',

    'colonnes' => [

        array('nom' => 'Référence fournisseur', 'valeur' => 'reference', 'ordre' => 0),
        array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 0),
        array('nom' => 'Conditionnement', 'valeur' => 'conditionnement_id', 'ordre' => 0),
    ],

    'calculs' => [],

    'filtres' => [
        array('nom_sql' => 'article_id'),
        array('nom_sql' => 'conditionnement_id'),
        array("nom_sql" => "quantite", "type_element" => "conditionnement", "champ_de_liaison" => "conditionnement_id"),
    ],
];