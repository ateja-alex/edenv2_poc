<?php

return [
    'table_libre' => [
        'nom_table' => "Profil droit élément",
        'nom_table_sql' => "profil_droits_element",
        'description' => "",
        'element' => "profil droits élément",
        'type_element' => "profil_droits_element",
        'element_pluriel' => "profil droits éléments",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1
    ],
    'champs_libres' => [
        'profil_id' => [
            'nom' => 'Profil',
            'type' => 42,
            'type_element_ajax' => 'profil'
        ],
        'entite_id' => [
            'nom' => 'Entité',
            'type' => 42,
            'type_element_ajax' => 'entite'
        ],
        'type_element' => [
            'nom' => 'Type élément'
        ],
        'nom_sql' => [
            'nom' => 'Nom sql'
        ],
        'lecture' => [
            'nom' => 'Lecture',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'creation' => [
            'nom' => 'Création',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'modification' => [
            'nom' => 'Modification',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'suppression' => [
            'nom' => 'Suppression',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'modification_en_masse' => [
            'nom' => 'Modification en masse',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'suppression_en_masse' => [
            'nom' => 'Suppression en masse',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
        'comptabilisation' => [
            'nom' => 'Comptabilisation',
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => "toggle",
        ],
    ],
];