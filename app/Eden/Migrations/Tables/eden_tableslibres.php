<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'nom_table' => [

        'type' => 'string',
    ],
	'nom_table_sql' => [

        'type' => 'string',
    ],
	'description' => [

        'type' => 'text',
    ],
	'feminin' => [

        'type' => 'string',
        'taille' => 1,
    ],
	'element' => [

        'type' => 'string',
    ],
	'type_element' => [

        'type' => 'string',
    ],
	'element_pluriel' => [

        'type' => 'string',
    ],
	'fiche' => [

        'type' => 'integer',
    ],
	'disponible_recherche_rapide' => [

        'type' => 'integer',
    ],
    'creation_rapide' => [

        'type' => 'integer',
    ],
    'envoyer_email' => [

        'type' => 'integer',
    ],
	'module' => [

        'type' => 'string',
    ],
    'parametre' => [

        'type' => 'integer',
    ],
    'affichage_recherche' => [

        'type' => 'string',
    ],
    'affichage_fiche_type' => [

        'type' => 'string',
    ],
    'affichage_dans_liste' => [

        'type' => 'string',
    ],
    'affichage_pour_select' => [

        'type' => 'string',
    ],
    'affichage_extranet' => [

        'type' => 'string',
    ],
    'affichage_dans_kanban' => [

        'type' => 'string',
    ],
    'affichage_dedoublonnage' => [

        'type' => 'string',
    ],
    'template_responsive' => [

        'type' => 'text',
    ],
    'editable_client' => [

        'type' => 'integer',
    ],
    'categorie' => [

        'type' => 'text',
    ],
    'icone_fontawesome' => [

        'type' => 'text',
    ],
    'synchro_bibliotheque' => [

        'type' => 'text',
    ],
    'corbeille' => [
        'type' => 'integer',
    ],
    'vue_sql' => [
        'type' => 'integer',
    ],
    'type_profil_extranet' => [

        'type' => 'text',
    ],
    'champ_profil_extranet' => [

        'type' => 'text',
    ],
    'valeurs_forcees_creation_extranet' => [

        'type' => 'text',
    ],
    'acces_extranet' => [

        'type' => 'integer',
    ],
    'index_traduction' => [

        'type' => 'string',
    ],
    'table_systeme' => [

        'type' => 'integer',
    ],
    'id_element_recherche' => [

        'type' => 'integer',
    ],
    'non_logue' => [
        'type' => 'integer',
    ],
    'recherche_globale_like' => [
        'type' => 'integer',
    ],
    'dedoublonnage' => [
        'type' => 'integer',
    ],
    'affichage_planning' => [
        'type' => 'string',
    ],
    'affichage_calendrier' => [
        'type' => 'string',
    ],

];

