<?php


return [

    'type_element' => 'parametrage_destinataire_erp',
    'fiche' => 'notification_manuelle',
    'cle_etrangere' => 'notification_manuelle_id',

    'colonnes' => [
        array('nom' => 'Valeur', 'valeur' => '', 'methode' => "valeur_liste", 'ordre' => 1),
    ],
    'calculs' => [],

    'filtres' => [

        array('nom_sql' => 'type'),
    ],
];