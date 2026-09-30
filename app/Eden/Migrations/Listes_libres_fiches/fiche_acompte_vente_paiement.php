<?php

return [

    'type_element' => 'paiement',
    'fiche' => 'acompte_vente',
    'cle_etrangere' => 'id_document',
    'formulaire_libre' => 'formulaire_documents_paiement_encaissement',

    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 1),
        array('nom' => 'Titre', 'valeur' => 'titre', 'ordre' => 2),
        array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 3),
        array('nom' => 'Mode', 'valeur' => 'mode_paiement_id', 'ordre' => 4),

    ],

    'calculs' => [],

    'filtres' => [

        array('nom_sql' => 'date'),
        array('nom_sql' => 'mode_paiement_id'),
    ],
];