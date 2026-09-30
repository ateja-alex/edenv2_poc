<?php

return [
    'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Détails ligne devis achat',
	'description' => "Liste affichée pour le détail des lignes de devis achat",
	'ordre' => 100,
	'inactif' => 0,

	// paramètres de la liste libre
	'liste_libre' => [
        'type_element' => 'devis_achat_lignes',
        'desactiver_options' =>1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array("nom" => "Code article", "valeur" => "code_article", "ordre" => "0"),
            array("nom" => "Désignation", "valeur" => "designation", "ordre" => "1"),
            array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "2"),
            array("nom" => "PU HT", "valeur" => "tarif", "ordre" => "3"),
            array("nom" => "Remise", "valeur" => "remise", "ordre" => "4"),
            array("nom" => "Total HT", "methode" => "total", "arguments" => "ht", "ordre" => "5"),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];