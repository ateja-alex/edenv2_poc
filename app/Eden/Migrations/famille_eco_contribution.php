<?php

return [
    'table_libre' => [
        'nom_table' => "Famille d'éco-contribution",
        'nom_table_sql' => "famille_eco_contribution",
        'description' => "",
        'feminin' => "",
        'element' => "famille d'éco-contribution",
        'type_element' => "famille_eco_contribution",
        'element_pluriel' => "familles d'éco-contribution",
        'fiche' => 1,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#eco_organisme_id#, #nom#',
    ],
    'champs_libres' => [
        'eco_organisme_id' => [
            'nom' => "Eco-organisme",
            'type' => 42,
            'type_element_ajax' => 'eco_organisme',
            'obligatoire' => 1,
        ],
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
        ]
    ],
];