<?php

return [

    'id_cl'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'type_element' => [

        'type' => 'string',
    ],
	'nom' => [

        'type' => 'string',
    ],
	'nom_en' => [

        'type' => 'string',
    ],
	'nom_es' => [

        'type' => 'string',
    ],
	'nom_de' => [

        'type' => 'string',
    ],
	'format_champ' => [

        'type' => 'string',
    ],
	'nom_sql' => [

        'type' => 'string',
    ],
	'inactif' => [

        'type' => 'integer',
    ],
	'type' => [

        'type' => 'integer',
    ],
    'type_reference' => [

        'type' => 'integer',
    ],
	'liste_choix' => [

        'type' => 'integer',
    ],
	'ajout_valeur_volee_inactif' => [

        'type' => 'integer',
    ],
	'recherche' => [

        'type' => 'integer',
    ],
	'obligatoire' => [

        'type' => 'integer',
    ],
	'lecture_seule' => [

        'type' => 'integer',
    ],
	'afficher_sur_formulaire' => [

        'type' => 'integer',
    ],
    'modifier_en_masse' => [

        'type' => 'integer',
    ],
	'modification_post_validation' => [

        'type' => 'integer',
    ],
	'valeur_defaut' => [

        'type' => 'text',
    ],
	'type_element_ajax' => [

        'type' => 'string',
    ],
	'aide' => [

        'type' => 'text',
    ],
	'nombre_max_caracteres' => [

        'type' => 'integer',
    ],
	'ordre' => [

        'type' => 'integer',
    ],
	'taille_libelle' => [

        'type' => 'integer',
    ],
	'taille_champ' => [

        'type' => 'integer',
    ],
	'multilingue' => [

        'type' => 'integer',
    ],
    'table_pivot' => [

        'type' => 'string',
    ],
    'unique' => [

        'type' => 'integer',
    ],
    'unique_entite' => [

        'type' => 'integer',
    ],
    'badge_cliquable' => [
        'type' => 'integer',
    ],
    'badge_filtre' => [
        'type' => 'integer',
    ],
    'classes_css' => [
        'type' => 'string',
    ],
    'classes_js' => [
        'type' => 'string',
    ],
    'contenu' => [
        'type' => 'text',
    ],
    'conditions_v_show_manuelle' => [
        'type' => 'string',
    ],
    'conditions_v_if_manuelle' => [
        'type' => 'string',
    ],
    'index_traduction' => [
        'type' => 'string',
    ],
    'afficher_en_responsive_dans_listes' => [
        'type' => 'integer',
    ],
    'colonne_source' => [
        'type' => 'string',
    ],
    'visibilite' => [
        'type' => 'string',
    ],
    'correspondance_fiche_tiers' => [
        'type' => 'string',
    ],
    'filtrage' => [
        'type' => 'longtext',
    ],
    'standard' => [
        'type' => 'integer',
    ],

	// les données calculées
	'donnee_calculee_depuis' => [
        'type' => 'string',
    ],
    'donnee_calculee_requete' => [
        'type' => 'string',
    ],
    'donnee_calculee_champ_maj' => [
        'type' => 'string',
    ],
    'recalcul_quotidien' => [
        'type' => 'integer',
    ],

    'nom_pj' => [
        'type' => 'text',
    ],
    'desactiver_creation_a_la_volee' => [
        'type' => 'integer',
    ],
    'cacher_sans_valeur' => [
        'type' => 'integer',
    ],
    'contenu_tableau' => [
        'type' => 'longtext',
    ],
    'type_element_origine' => [
        'type' => 'string',
    ],
    'nom_sql_origine' => [
        'type' => 'string',
    ],
    'index' => [
        'type' => 'integer',
    ],
    'doit_etre_plus_petit_que' => [
        'type' => 'string',
    ],
    'nombre_decimale' => [
        'type' => 'integer',
    ],
    'champ_liste_libre_parent' => [
        'type' => 'string',
    ],
    'champ_liste_libre_liaisons' => [
        'type' => 'longtext',
    ],
    'decimal_separateur_milliers' => [
        'type' => 'string',
    ],
    'type_fichier' => [
        'type' => 'string',
    ],
    'champ_systeme' => [

        'type' => 'integer',
    ],
    'ne_pas_loguer' => [
        'type'=> 'integer',
    ],
];

