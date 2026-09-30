<?php
return [

    "type_element" => "transfert_inter_entrepot_articles",
    "fiche" => "transfert_inter_entrepot",
    "cle_etrangere" => "transfert_inter_entrepot_id",
    "formulaire_libre" => "",
    "colonnes" => [
        array('nom' => '#', 'valeur' => 'id', "lien_vers_element" => "0", 'ordre' => 0),
        array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
        array('nom' => "Quantité", 'valeur' => 'quantite', 'ordre' => 2),
    ],
    'calculs' => [
    ],
    'filtres' => [
    ],
];