<?php

return [
	'id_rapport' => 'fiche_article_article_recurrent',
	'type_element' => 'article_recurrent',
    'fiche' => 'article',
    'cle_etrangere' => 'article_id',

	'desactiver_options' => '1',
	"colonnes" => [
            array("nom" => "Document", "ordre" => "1", "methode" => "lien_document"),
                array("nom" => "Client", "valeur" => "client_id", "ordre" => "2"),
                array("nom" => "Depuis le", "valeur" => "depuis_le", "ordre" => "3"),
                array("nom" => "Jusqu'au", "valeur" => "jusquau", "ordre" => "4"),
                array("nom" => "Mode récurrence", "valeur" => "mode_recurrence", "ordre" => "5"),
                array("nom" => "Tarif", "valeur" => "tarif", "ordre" => "6"),
                array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "7"),
                array("nom" => "Actif", "valeur" => "actif", "ordre" => "8"),
    ],
    'calculs' => [],
    'filtres' => [],
    ];