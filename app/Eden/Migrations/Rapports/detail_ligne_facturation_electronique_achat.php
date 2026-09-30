<?php

return [
    'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Détails ligne facturation électronique achat',
	'description' => "Liste affichée pour le détail des statuts de cycle de vie posés sur une facturation électronique achat",
	'ordre' => 100,
	'inactif' => 0,

	'liste_libre' => [
        'type_element' => 'facturation_electronique_cycle_de_vie',
        'desactiver_options' => 1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array("nom" => "Statut", "valeur" => "", "type" => "champ", "champ" => "statut_achat", "ordre" => "0"),
            array("nom" => "Motif", "valeur" => "", "type" => "champ", "champ" => "motif", "ordre" => "1"),
            array("nom" => "Statut d'envoi", "valeur" => "", "type" => "champ", "champ" => "statut_envoi", "ordre" => "2"),
            array("nom" => "Motif erreur d'envoi", "valeur" => "", "type" => "champ", "champ" => "motif_erreur_envoi", "ordre" => "3"),
            array("nom" => "Date de l'évènement", "valeur" => "", "type" => "champ", "champ" => "date_evenement", "ordre" => "4"),
            array("nom" => "Date de pose", "valeur" => "", "type" => "champ", "champ" => "cree_le", "ordre" => "5"),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];
