<?php

return [
    'categorie' => 'gestion_commerciale',
    'icone' => 'table',
    'titre' => 'Détails ligne commande vente lignes',
    'description' => "Liste affichée pour le détail des lignes de commande vente lignes",
    'ordre' => 100,
    'inactif' => 0,

    // paramètres de la liste libre
    'liste_libre' => [
        'type_element' => 'commande_vente_lignes',
        'desactiver_options' => 1,
        'desactiver_creation' => 1,
        'colonnes' => [
            array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 1),
            array('nom' => 'Quantité', 'valeur' => 'quantite', 'ordre' => 2),
            array('nom' => 'PU', 'valeur' => 'tarif', 'ordre' => 3),
        ],
        'calculs' => [
        ],
        'filtres' => [
        ],
    ]
];