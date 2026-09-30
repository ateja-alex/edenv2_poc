<?php

    return [
        'table_libre' => [
            'nom_table' => "Traduction index",
            'nom_table_sql' => "traduction_index",
            'description' => "",
            'feminin' => "",
            'element' => "traduction index",
            'type_element' => "traduction_index",
            'element_pluriel' => "traductions index",
            'fiche' => 0,

            'creation_rapide' => 0,
            'disponible_recherche_rapide' => 0,
            'table_systeme' => 1,
        ],
        'champs_libres' => [
            'index' => [
                'nom' => "Index",
                'unique' => true,
            ],
            'categorie' => [
                'nom' => "Catégorie",
                'type' => 20,
                'liste_choix' => 590,
            ],
        ],
    ];