<?php

return [
    'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Détails ligne note de frais',
	'description' => "Liste affichée pour le détail des lignes de note de frais",
	'ordre' => 100,
	'inactif' => 0,

	// paramètres de la liste libre
	'liste_libre' => [
        'type_element' => 'note_de_frais_lignes',
        'desactiver_options' =>1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 0, 'lien_vers_element' => 1),
            array('nom' => 'HT', 'valeur' => 'montant_ht', 'ordre' => 1),
            array('nom' => 'Taux de tva', 'valeur' => 'taux_tva', 'ordre' => 2),
            array('nom' => 'TTC', 'valeur' => 'montant_ttc', 'ordre' => 3),
            array("nom" => "Montant remboursé", "valeur" => "", "ordre" => "5", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "champ", "champ" => "montant_rembourse", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];