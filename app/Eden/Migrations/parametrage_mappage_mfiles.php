<?php

return [
    'table_libre' => [
        'nom_table' => "Paramétrage mappage M-Files",
        'nom_table_sql' => "parametrage_mappage_mfiles",
        'description' => "Permet de définir quels types d'éléments vont être synchronisés dans M-Files",
        'feminin' => "",
        'element' => "parametrage_mappage_mfiles",
        'type_element' => "parametrage_mappage_mfiles",
        'element_pluriel' => "parametrages_mappage_mfiles",
        'fiche' => 1,

        'disponible_recherche_rapide' => 1,
        'creation_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#table_libre#, #classe_mfiles#',
    ],
    'champs_libres' => [
        'table_libre' => [
            'nom' => "Table libre Eden",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'classe_mfiles' => [
            'nom' => "Classe M-Files",
            'obligatoire' => 1,
            'recherche' => 1,
            'lecture_seule' => 1,
        ],
        'derniere_synchronisation_mfiles' => [
            'nom' => "Dernière synchronisation",
            'recherche' => 1,
            'type' => 4,
        ],
        'objet_mfiles' => [
            'nom' => "Objet M-Files",
        ],
        'classe_document_mfiles' => [
            'nom' => "Classe de la pièce jointe M-Files",
        ],
        'attribut_liaison_document_mfiles' => [
            'nom' => "Attribut de liaison de la pièce jointe M-Files",
        ],
        'type_attribut_liaison' => [
            'nom' => "Type de l'attribut de liaison de la pièce jointe M-Files",
            'type' => 2,
        ],
    ],
];
