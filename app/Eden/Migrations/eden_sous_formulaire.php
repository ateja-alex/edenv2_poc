<?php

return [
    'table_libre' => [
        'nom_table' => "Sous formulaire",
        'nom_table_sql' => "eden_sous_formulaire",
        'description' => "",
        'feminin' => "",
        'element' => "Sous-formulaire",
        'type_element' => "eden_sous_formulaire",
        'element_pluriel' => "Sous-formulaires",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'editable_client' => 0,
        'table_systeme' => 1,
    ],
    'champs_libres' => [
        'formulaire_id' => [
            'nom' => "Formulaire",
            'type' => 2,
        ],
        'type_element_enfant' => [
            'nom' => "Type_element enfant",
            'type' => 0,
            'obligatoire' => 1,
        ],
        'champ_liaison' => [
            'nom' => "Champ liaison",
            'type' => 0,
            'obligatoire' => 1,
        ],
        'nom_sous_formulaire' => [
            'nom' => "Nom du formulaire",
            'type' => 0,
            'obligatoire' => 1,
        ],
        'nom_formulaire_parent' => [
            'nom' => "Nom du formulaire parent",
            'type' => 0,
        ],
        'nom_donnee_different' => [
            'nom' => "Nom des données différent du type_element ?",
            'type' => 20,
            'liste_choix' => 14,
            "valeur_defaut" => 0,
        ],
        'type_element_remplacement' => [
            'nom' => "Type_element remplacement",
            'type' => 0,
        ],
        'data_vue' => [
            'nom' => "Data vue",
            'type' => 6,
        ],
        'remplacement_supplementaire' => [
            'nom' => "Remplacement supplémentaire",
            'type' => 6,
        ],
        'optionnel' => [
            'nom' => "Optionnel",
            'type' => 20,
            'liste_choix' => 14,
            "valeur_defaut" => 0,
        ],
        'unique' => [
            'nom' => "Unique",
            'type' => 20,
            'liste_choix' => 14,
            "valeur_defaut" => 0,
        ],
        'nombre' => [
            'nom' => "Nombre",
            'type' => 2
        ],
    ],
];
