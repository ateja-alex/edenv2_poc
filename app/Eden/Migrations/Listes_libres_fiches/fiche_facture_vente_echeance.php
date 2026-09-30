<?php

return [
	'type_element' => 'echeance',
	'fiche' => 'facture_vente', 
	'cle_etrangere' => 'element_id',
	"colonnes" => [
        array('nom' => 'Titre', 'valeur' => 'titre', 'ordre' => 1),
        array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
        array('nom' => 'Montant', 'valeur' => 'montant', 'ordre' => 3),
    ],
    'calculs' => [
    ],
    'filtres' => [
    ],
];