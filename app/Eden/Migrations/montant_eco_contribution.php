<?php

return [
    'table_libre' => [
        'nom_table' => "Montant d'éco-contribution",
        'nom_table_sql' => "montant_eco_contribution",
        'description' => "",
        'feminin' => "",
        'element' => "montant d'éco-contribution",
        'type_element' => "montant_eco_contribution",
        'element_pluriel' => "montants d'éco-contribution",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'categorie_eco_contribution_id' => [
            'nom' => "Catégorie",
            'type' => 42,
            'type_element_ajax' => 'categorie_eco_contribution',
            'obligatoire' => 1
        ],
        'montant' => [
            'nom' => "Montant",
            'type' => 3,
            'format_champ' => 'monetaire',
            'nombre_decimale' => 2,
            'obligatoire' => 1
        ],
        'date_debut' => [
            'nom' => "Date de début d'application",
            'type' => 4,
        ],
        'date_fin' => [
            'nom' => "Date de fin d'application",
            'type' => 4,
        ],
    ],
];