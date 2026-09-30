<?php

$specifique = [];

if(file_exists(storage_path('app/eden_fonctionnalites_pdf_par_defaut.php'))) {

    $specifique = include(storage_path('app/eden_fonctionnalites_pdf_par_defaut.php'));
}

$standard =  [

    'avoir_achat' => '',
    'avoir_vente' => '',
    'acompte_achat' => '',
    'acompte_vente' => '',
    'bl_achat' => '',
    'bl_vente' => '',
    'commande_achat' => '',
    'commande_vente' => '',
    'devis_achat' => '',
    'devis_vente' => '',
    'facture_achat' => '',
    'facture_vente' => '',
    'bon_preparation_vente' => '',
    'bon_retour_vente' => '',
    'bon_retour_achat' => '',
];

$definitive = array();

foreach($standard as $index => $valeur_standard) {

    $definitive[$index] = $valeur_standard;
}

foreach($specifique as $index => $valeur_specifique) {

    $definitive[$index] = $valeur_specifique;
}

return $definitive;
