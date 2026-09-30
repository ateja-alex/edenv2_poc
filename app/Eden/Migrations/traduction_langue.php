<?php

    return [
        'table_libre' => [
            'nom_table' => "Traduction langue",
            'nom_table_sql' => "traduction_langue",
            'description' => "",
            'feminin' => "",
            'element' => "traduction langue",
            'type_element' => "traduction_langue",
            'element_pluriel' => "traductions langues",
            'fiche' => 0,

            'creation_rapide' => 0,
            'disponible_recherche_rapide' => 0,
            'table_systeme' => 1,
            'affichage_dans_liste' => '#nom#',
        ],
        'champs_libres' => [
            'code' => [
                'nom' => "Code",
                'unique' => true,
                'recherche' => true,
            ],
            'nom' => [
                'nom' => "Nom",
                'recherche' => true,
            ],
            'disponible_interface' => [
                'nom' => "Disponible sur l'interface",
                'type' => 20,
                'liste_choix' => 14,
                'format_champ' => 'toggle',
            ],
        ],
    ];