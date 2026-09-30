<?php


return [

    'type_element' => 'parametrage_piece_jointe_email',
    'fiche' => 'modele_email',
    'cle_etrangere' => 'modele_email_id',

    'colonnes' => [
        array('nom' => 'Niveau', 'valeur' => 'niveau', 'ordre' => 1),
        array('nom' => 'Valeur', 'valeur' => '', 'methode' => "valeur_liste", 'ordre' => 2),
    ],
    'calculs' => [],

    'filtres' => [],
];