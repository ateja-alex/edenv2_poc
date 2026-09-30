<?php

return [

    'id'=> [

        'type' => 'integer',
        'cle_primaire' => true,
	],
	'liste_libre_id' => [

        'type' => 'integer',
    ],
	'nom' => [

        'type' => 'string',
    ],
	'valeur' => [

        'type' => 'string',
    ],
	'ordre' => [

        'type' => 'integer',
    ],
	'lien_vers_element' => [

        'type' => 'integer',
    ],
	'methode' => [

        'type' => 'string',
    ],
	'lien_vers_autre_element' => [

        'type' => 'string',
    ],
    'tri_desactive' => [
        'type' => 'integer',
    ],
    'tri_par_defaut' => [
        'type' => 'integer',
    ],
    'sens_tri_par_defaut' => [
        'type' => 'integer',
    ],
    'index_traduction' => [
        'type' => 'string',
    ],
    'type' => [
        'type' => 'string',
    ],
    'champ' => [
        'type' => 'string',
    ],
    'responsive' => [
        'type' => 'integer',
    ],
    'caracteres_max' => [
        'type' => 'integer',
    ],
    'standard' => [
        'type' => 'integer',
    ],
    'retour_a_la_ligne_impossible' => [
        'type' => 'integer',
    ],
    'couleur_colonne' => [
        'type' => 'string',
    ],
    'alignement_colonne' => [
        'type' => 'string',
    ],
    'arguments' => [
        'type' => 'string',
    ],
    'afficher_formulaire_element' => [
        'type' => 'integer',
    ],  
    'afficher_avatars_utilisateurs' => [
        'type' => 'integer',
    ],
    'source_calcul' => [
        'type' => 'string',
    ],
    'type_calcul' => [
        'type' => 'string',
    ],
    'champ_calcul' => [
        'type' => 'string',
    ],
    'groupement_calcul' => [
        'type' => 'string',
    ],
    'periodicite_calcul' => [
        'type' => 'string',
    ],
];