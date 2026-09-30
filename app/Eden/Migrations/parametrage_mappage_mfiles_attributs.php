<?php

return [
    'table_libre' => [
        'nom_table' => "Paramétrage mappage des attributs M-Files",
        'nom_table_sql' => "parametrage_mappage_mfiles_attributs",
        'description' => "Permet de définir à quels champs libres correspondent les attributs dans M-Files",
        'feminin' => "",
        'element' => "parametrage_mappage_mfiles_attributs",
        'type_element' => "parametrage_mappage_mfiles_attributs",
        'element_pluriel' => "parametrage_mappage_mfiles_attributs",
        'fiche' => 1,

        'disponible_recherche_rapide' => 1,
        'creation_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#champ_libre_eden#, #attribut_mfiles#, #parametrage_mappage_mfiles_id#',
    ],
    'champs_libres' => [
        'champ_libre_eden' => [
            'nom' => "Champ libre Eden",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'attribut_mfiles' => [
            'nom' => "Attribut M-Files",
            'type' => 2,
            'obligatoire' => 1,
            'recherche' => 1,
            'lecture_seule' => 1,
        ],
        'parametrage_mappage_mfiles_id' => [
            'nom' => "Classe M-Files",
            'type' => 42,
            'obligatoire' => 1,
            'recherche' => 1,
            'type_element_ajax' => 'parametrage_mappage_mfiles',
        ],
        'type' => [
            'nom' => "Type",
            'type' => 2,
        ],
    ],
];
