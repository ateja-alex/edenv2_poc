<?php

return [
    'categorie' => 'gestion_commerciale',
	'icone' => 'link',
	'titre' => 'Rapprochement facture achat',
	'description' => "Liste des factures d'achat candidates au rapprochement avec une facturation électronique achat",
	'ordre' => 100,
	'inactif' => 0,

	'liste_libre' => [
        'type_element' => 'facture_achat',
        'desactiver_creation' => 1,
        'desactiver_actions' => 1,
        'desactiver_export' => 1,
        'desactiver_recherche_avancee' => 1,
        'desactiver_options_individuelle' => '["zoom","lien","comptabiliser","dupliquer","pdf","retablir"]',
        'colonnes' => [
            array("nom" => "Référence", "valeur" => "reference_document", "ordre" => "0"),
            array("nom" => "Date", "valeur" => "date", "ordre" => "1", "tri_par_defaut" => "1", "sens_tri_par_defaut" => "1"),
            array("nom" => "Fournisseur", "valeur" => "fournisseur_id", "ordre" => "2"),
            array("nom" => "Référence fournisseur", "valeur" => "reference_fournisseur", "ordre" => "3"),
            array("nom" => "Montant HT", "valeur" => "montant_document_ht", "ordre" => "4"),
            array("nom" => "Montant TTC", "valeur" => "montant_document_ttc", "ordre" => "5"),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];
