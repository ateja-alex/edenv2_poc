<?php

return [

    'desactiver_options_individuelle' => '["supprimer","dupliquer"]',
    'desactiver_actions' => 1,
    'desactiver_export' => 1,
    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'ID externe', 'valeur' => 'id_externe', 'ordre' => 1),
        array('nom' => 'Utilisateur', 'valeur' => 'utilisateur', 'ordre' => 2),
        array('nom' => 'Message d\'erreur', 'valeur' => 'message_erreur', 'ordre' => 3),
        array('nom' => 'Synchro externe', 'valeur' => 'synchro_externe', 'ordre' => 4),
    ],
    'calculs' => [],
    'filtres' => [
        array('nom_sql' => 'utilisateur'),
        array('nom_sql' => 'id_externe'),
    ],
];