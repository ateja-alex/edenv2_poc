<?php

return [
    'table_libre'   => [
        'nom_table'                   => "Pièce jointe",
        'nom_table_sql'               => "element_piece_jointe",
        'description'                 => "",
        'feminin'                     => "",
        'element'                     => "pièce jointe",
        'type_element'                => "element_piece_jointe",
        'element_pluriel'             => "pièces jointes",
        'fiche'                       => 0,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide'             => 0,
        'table_systeme'               => 1,
    ],
    'champs_libres' => [
        'type_element' => [
            'nom' => 'Type élément',
        ],
        'element_id' => [
            'nom' => 'Element',
            'type' => 2
        ],
        'nom' => [
            'nom' => 'Nom',
        ],
        'chemin' => [
            'nom' => 'Chemin',
        ],
        'titre' => [
            'nom' => 'Titre',
        ],
        'ordre' => [
            'nom' => 'Ordre',
            'type' => 2
        ],
        'lier_au_document' => [
            'nom' => 'Lier au document',
        ],
        'dossier_parent' => [
            'nom' => 'Dossier parent',
            'type' => 42,
            'type_element_ajax' => "dossier_bibliotheque",
        ],
        'stockage_externe' => [
            'nom' => 'Stockage externe',
            'type' => 20,
            'liste_choix' => 591
        ],
        'stockage_externe_id' => [
            'nom' => 'Stockage externe id',
            'type' => 2
        ],
        'document_commercial' => [
            'nom' => 'Document commercial',
            'type' => 20,
            'liste_choix' => 14
        ],
        'type_element_createur' => [
            'nom' => "Type élément créateur",
            'type' => 21,
            'contenu' => '[{"type_element":"utilisateur","valeur":true},{"type_element":"client","valeur":true},{"type_element":"contact","valeur":true}]',
        ],
        'element_id_createur' => [
            'nom' => "Élément ID créateur",
            'type' => 22,
            'contenu' => 'type_element_createur'
        ],
        'disponible_extranet' => [
            'nom' => 'Disponible pour l\'extranet',
            'type' => 20,
            'liste_choix' => 14,
            'modifier_en_masse' => 1,
        ],
    ],
];
