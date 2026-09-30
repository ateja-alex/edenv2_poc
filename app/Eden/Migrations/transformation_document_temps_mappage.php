<?php

return [
    'table_libre' => [
        'nom_table' => "Mappage élément vers document",
        'nom_table_sql' => "transformation_document_temps_mappage",
        'description' => "",
        'feminin' => "",
        'element' => "Mappage élément vers document",
        'type_element' => "transformation_document_temps_mappage",
        'element_pluriel' => "Mappages élément vers documents",
        'parametre' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
    ],
    'champs_libres' => [
        'champ_source' => [
            'nom' => "Champ d'origine",
            'obligatoire' => 1,
        ],
        'champ_destination' => [
            'nom' => "Champ de destination",
            'obligatoire' => 1,
        ],
        'modele_id' => [
            'nom' => 'Modèle parent',
            'type' => 42,
            'type_element_ajax' => 'transformation_document_temps_modele',
            'obligatoire' => 1,
        ],
        'champ_dur' => [
            'nom' => "Valeur du champ en dur",
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => 'toggle',
        ],
    ],
];