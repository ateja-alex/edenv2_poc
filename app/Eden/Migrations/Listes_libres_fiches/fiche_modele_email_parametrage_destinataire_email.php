<?php


return [

    'type_element' => 'parametrage_destinataire_email',
    'fiche' => 'modele_email',
    'cle_etrangere' => 'modele_email_id',

    'colonnes' => [
        array('nom' => 'Type', 'valeur' => 'type', 'ordre' => 1),
        array('nom' => 'Niveau', 'valeur' => 'niveau', 'ordre' => 2),
        array('nom' => 'Valeur', 'valeur' => '', 'methode' => "valeur_liste", 'ordre' => 3),
    ],
    'calculs' => [],

    'filtres' => [

        array('nom_sql' => 'type'),
    ],
];