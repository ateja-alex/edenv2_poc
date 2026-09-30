<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'type_element' => [

        'type' => 'string',
    ],
    'id_rapport' => [

        'type' => 'string',
    ],
	'filtres_appliques' => [

        'type' => 'text',
    ],
    'limit' => [

        'type' => 'integer',
    ],
    'orderby' => [

        'type' => 'string',
    ],
    'orderby_sens' => [

        'type' => 'string',
    ],
    'fiche' => [

        'type' => 'string',
    ],
    'cle_etrangere' => [

        'type' => 'string',
    ],
    'cle_primaire' => [

        'type' => 'string',
    ],
    'type_element_primaire' => [

        'type' => 'string',
    ],
    'avec_inactifs' => [

        'type' => 'integer',
    ],
    'bloquer_tri' => [

        'type' => 'integer',
    ],
	'affichage_compact' => [	

        'type' => 'integer',	
    ],
    'export' => [
        'type' => 'integer'
    ],
    'desactiver_filtres' => [
        'type' => 'integer'
    ],
    'desactiver_recherche_avancee' => [
        'type' => 'integer'
    ],
    'desactiver_actions' => [
        'type' => 'integer'
    ],
    'desactiver_options' => [
        'type' => 'integer'
    ],
    'desactiver_export' => [
        'type' => 'integer'
    ],
    'desactiver_recherche' => [
        'type' => 'integer'
    ],
    'desactiver_creation' => [
        'type' => 'integer'
    ],
    'desactiver_drag_drop_kanban' => [
        'type' => 'integer'
    ],
	'condition_desactiver_creation' => [
        'type' => 'string'
    ],
    'formulaire_libre' => [
        'type' => 'string'
    ],
    'desactiver_kanban_sans_valeur' => [
        'type' => 'integer'
    ],
    'desactiver_options_individuelle' => [
        'type' => 'longtext'
    ],
    'options_mobile' => [
        'type' => 'longtext'
    ],
    'desactiver_actions_individuelle' => [
        'type' => 'longtext'
    ],
    'lignes_par_page' => [
        'type' => 'integer',
    ],
    'creation_taches_en_masse' => [
        'type' => 'integer'
    ],
    'formulaire_modale' => [
        'type' => 'integer',
    ],
    'afficher_images' => [
        'type' => 'integer',
    ],
    'modele_email_defaut' => [
       'type' => 'integer',
    ],
    'afficher_calculs_haut_liste' => [
        'type' => 'integer',
    ],
    'tri_kanban' => [
        'type' => 'longtext',
    ],
    'affichage_kanban_vertical' => [
        'type' => 'integer',
    ],
];

