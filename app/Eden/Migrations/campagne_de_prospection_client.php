<?php

return [
    'table_libre' => [
        'nom_table' => "Campagne de prospection client",
        'nom_table_sql' => "campagne_de_prospection_client",
        'description' => "",
        'feminin' => "e",
        'element' => "campagne de prospection client",
        'type_element' => "campagne_de_prospection_client",
        'element_pluriel' => "campagnes de prospection client",
        'fiche' => "0",
        'disponible_recherche_rapide' => "0",
        'creation_rapide' => "0",
        'envoyer_email' => "",
        'module' => "",
        'parametre' => "",
        'affichage_recherche' => "",
        'affichage_fiche_type' => "",
        'affichage_dans_liste' => "",
        'template_responsive' => "",
        'editable_client' => "",
        'categorie' => "",
        'icone_fontawesome' => "",
        'synchro_bibliotheque' => "",
        'corbeille' => "",
        'vue_sql' => "0",
        'type_profil_extranet' => "",
        'champ_profil_extranet' => "",
        'acces_extranet' => 0,
    ],
    'champs_libres' => [
        'campagne_de_prospection_id' => [
            'nom' => "Campagne de prospection ID",
            'type' => "42",
            'type_element_ajax' => 'campagne_de_prospection',
        ],
        'client_id' => [
            'nom' => "Client ID",
            'type' => "42",
            'type_element_ajax' => 'client',
        ],
        'entite_id' => [
            'nom' => "Entité",
            'type' => "42",
            'type_element_ajax' => 'entite',
        ],
        'utilisateur_id' => [
            'nom' => "Utilisateur",
            'type' => "42",
            'type_element_ajax' => 'utilisateur',
        ],
        'statut' => [
            'nom' => "Détails",
            'type' => 20,
            'liste_choix' => 610,
        ],
        'pdf' => [
            'nom' => "PDF",
            'type' => 0,
        ],
    ],
];