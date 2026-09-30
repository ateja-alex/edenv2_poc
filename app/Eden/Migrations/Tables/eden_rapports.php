<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'id_rapport' => [
        'type' => 'string',
    ],
	'categorie' => [
        'type' => 'string',
    ],
    'icone' => [
        'type' => 'string',
    ],
    'titre' => [
        'type' => 'string',
    ],
    'description' => [
        'type' => 'string',
    ],
    'ordre' => [
        'type' => 'string',
    ],
    'inactif' => [
        'type' => 'integer',
    ],
    'objectif' => [
        'type' => 'text',
    ],
    'parametrage' => [
        'type' => 'text',
    ],
    'type' => [
        'type' => 'string',
    ],
    'kanban' => [
        'type' => 'string',
    ],
    'kanban_colonnes' => [
        'type' => 'string',
    ],
    'kanban_colonne_somme' => [
        'type' => 'string',
    ],
    'kanban_entete_calcul_somme' => [
        'type' => 'integer',
    ],
    'kanban_entete_calcul_champ' => [
        'type' => 'string',
    ],
	'kanban_entete_calcul_nombre' => [
        'type' => 'integer',
    ],
	'kanban_entete_calcul_unite' => [
        'type' => 'string',
    ],
	'kanban_afficher_utilisateur_1' => [
        'type' => 'string',
    ],
	'kanban_afficher_utilisateur_2' => [
        'type' => 'string',
    ],
	'kanban_tri_champ' => [
        'type' => 'string',
    ],
	'kanban_tri_sens' => [
        'type' => 'string',
    ],

    'visualisation_carte' => [
        'type' => 'integer',
    ],
    'type_rapport' => [
        'type' => 'string',
    ],
    'parametrage_rapport_libre' => [
        'type' => 'longtext',
    ],
    'type_element' => [
        'type' => 'string',
    ],
    'extranet' => [
        'type' => 'integer',
    ],
    'icone_dans_rapport' => [
        'type' => 'string',
    ],
    'id_rapport_cible' => [
        'type' => 'string',
    ],
    'index_traduction' => [
        'type' => 'string',
    ],
    'liste_sur_fiche' => [
        'type' => 'integer',
    ],
    'export' => [
        'type' => 'integer',
    ],
    'intranet' => [
        'type' => 'integer',
    ],
    'rapport_sur_fiche' => [
        'type' => 'integer',
    ],
    'type_element_fiche' => [
        'type' => 'string',
    ],
    'cle_primaire' => [
        'type' => 'string',
    ],
    'cle_etrangere' => [
        'type' => 'string',
    ],
    'lien_rapport' => [
        'type' => 'string',
    ],
    'requete_sql' => [
        'type' => 'longtext'
    ],
    'toutes_les_colonnes' => [
        'type' => 'integer',
    ],
    'exclusion_colonnes' => [
        'type' => 'integer',
    ],
    'type_vue_carte' => [
        'type' => 'string',
    ],
];
