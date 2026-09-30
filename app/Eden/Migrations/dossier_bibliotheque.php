<?php

return [
    'table_libre' => [
        'nom_table' => "Dossiers de la bibliothéque",
        'nom_table_sql' => "dossier_bibliotheque",
        'description' => "",
        'feminin' => "",
        'element' => "dossier_bibliotheque",
        'type_element' => "dossier_bibliotheque",
        'element_pluriel' => "dossiers_bibliotheque",
        'fiche' => "0",
        'disponible_recherche_rapide' => "0",
        'creation_rapide' => "0",
        'module' => "",
        'envoyer_email' => "",
        'parametre' => "",
        'affichage_recherche' => "",
        'affichage_fiche_type' => "",
        'affichage_dans_liste' => "",
        'template_responsive' => "",
        'table_systeme' => 1,
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
        ],
        'chemin' => [
            'nom' => "Chemin",
        ],
        'dossier_parent' => [
            'type' => "42",
            'nom' => "Dossier parent",
            'type_element_ajax' => "dossier_bibliotheque",
        ],
        'type_element' => [
            'nom' => "Type Element",
        ],
        'element_id' => [
            'type' => "2",
            'nom' => "Element_id",
        ],
        'confidentiel' => [
            'type' => "20",
            'nom' => "Confidentiel",
            'liste_choix' => "14",
        ],
        'droit_entites' => [
            'nom' => "Droits des entités",
            'type' => 10,
            'type_element_ajax' => 'entite',
        ],
        'id_microsoft' => [
            'nom' => "ID Microsoft",
        ],
        'disponible_extranet' => [
            'nom' => 'Disponible pour l\'extranet',
            'type' => 20,
            'liste_choix' => 14,
            'modifier_en_masse' => 1,
        ],
    ],
];