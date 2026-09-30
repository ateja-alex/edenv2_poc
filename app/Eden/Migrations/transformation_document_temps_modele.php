<?php

return [
    'table_libre' => [
        'nom_table' => "Modèle de transformation en document des temps",
        'nom_table_sql' => "transformation_document_temps_modele",
        'description' => "",
        'feminin' => "",
        'element' => "Modèle de transformation en document des temps",
        'type_element' => "transformation_document_temps_modele",
        'element_pluriel' => "Modèles de transformation en document des temps",
        'fiche' => 1,
        'parametre' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_recherche' => "#nom#",
        'affichage_fiche_type' => "#nom#",
        'affichage_dans_liste' => "#nom#",
        'affichage_pour_select' => "#nom#",
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
        ],
        'niveau_detail' => [
            'nom' => "Niveau de détail",
            'type' => 20,
            'liste_choix' => 710,
        ],
        'type_element_cible' => [
            'nom' => 'Type élément à exporter',
            'type' => 21,
            'contenu' => '[{"type_element":"projet","valeur":true}]',
            'valeur_defaut' => "projet",
            'obligatoire' => 1,
        ],
        'type_document' => [
            'nom' => 'Type de document à créér',
            'type' => 20,
            'liste_choix' => 71,
            'valeur_defaut' => 6,
            'obligatoire' => 1,
        ],
        'dates_commentaires_lignes' => [
            'nom' => "Indiquer la/les date(s) dans les commentaires des lignes d'article",
            'type' => 20,
            'liste_choix' => 14,
            'format_champ' => 'toggle',
        ],
    ],
];