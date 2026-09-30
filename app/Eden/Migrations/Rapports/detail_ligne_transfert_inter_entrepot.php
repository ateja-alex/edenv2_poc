<?php

return [
    'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Détails ligne transfert inter entrepôt',
	'description' => "Liste affichée pour le détail des lignes de transfert inter entrepôt",
	'ordre' => 100,
	'inactif' => 0,

	// paramètres de la liste libre
	'liste_libre' => [
        'type_element' => 'mouvement_de_stock',
        'desactiver_options' =>1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
            array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
            array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 3),
            array('nom' => 'Entrepôt', 'valeur' => 'entrepot_id', 'ordre' => 4),
            array('nom' => 'Tags', 'valeur' => '', 'methode' => 'liste_tags', 'ordre' => 5),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];