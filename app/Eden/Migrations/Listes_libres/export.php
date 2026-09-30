<?php

return [

    'desactiver_actions' => "1",
    'desactiver_options' => "1",
    'desactiver_export' => "1",
    'desactiver_creation' => "1",
    'colonnes' => [

        array('nom' => 'Créateur', 'valeur' => 'element_id_createur', 'ordre' => 0),
        array('nom' => 'Date', 'valeur' => 'cree_le', 'ordre' => 1),
        array('nom' => 'Type d\'export', 'valeur' => 'type_export', 'ordre' => 2),
        array('nom' => 'ID de la liste', 'valeur' => 'id_liste', 'ordre' => 3),
        array('nom' => 'Terminé', 'valeur' => 'termine', 'ordre' => 4),
        array('nom' => 'Lien vers le fichier', 'methode' => 'retourne_lien_fichier', 'ordre' => 5),
        array('nom' => 'Traduction de l\'id liste', 'methode' => 'traduction_id_liste', 'ordre' => 6),
    ],
    'calculs' => [],
    'filtres' => [

        array('nom_sql' => 'type_element_createur'),
        array('nom_sql' => 'cree_le'),
    ],
];