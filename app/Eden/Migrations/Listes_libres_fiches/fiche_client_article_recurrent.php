<?php
return [
    'id_rapport' => 'fiche_client_article_recurrent',

    'type_element' => 'article_recurrent',
    'fiche' => 'client',
    'cle_etrangere' => 'client_id',

    'desactiver_options' => '1',
    "colonnes" => [
        array("nom" => "Document", "ordre" => "1", "methode" => "lien_document"),
        array("nom" => "Famille", "valeur" => "famille_id", "ordre" => "2"),
        array("nom" => "Article", "valeur" => "article_id", "ordre" => "3"),
        array("nom" => "Depuis le", "valeur" => "depuis_le", "ordre" => "4"),
        array("nom" => "Jusqu'au", "valeur" => "jusquau", "ordre" => "5"),
        array("nom" => "Mode récurrence", "valeur" => "mode_recurrence", "ordre" => "6"),
        array("nom" => "Tarif", "valeur" => "tarif", "ordre" => "7"),
        array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "8"),
        array("nom" => "Actif", "valeur" => "actif", "ordre" => "9"),
    ],
    'calculs' => [],
    'filtres' => [],
];