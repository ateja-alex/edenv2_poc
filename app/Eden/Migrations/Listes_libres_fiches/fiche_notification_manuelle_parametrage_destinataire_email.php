<?php


return [

    'type_element' => 'parametrage_destinataire_email',
    'fiche' => 'notification_manuelle',
    'cle_etrangere' => 'notification_manuelle_id',

    'colonnes' => [
        array('nom' => 'Type', 'valeur' => 'type', 'ordre' => 1),
        array('nom' => 'Valeur', 'valeur' => '', 'methode' => "valeur_liste", 'ordre' => 2),
    ],
    'calculs' => [],

    'filtres' => [

        array('nom_sql' => 'type'),
    ],
];