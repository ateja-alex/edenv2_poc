<?php
 return array (
  'table_libre' =>
  array (
    'nom_table' => 'Thèmes',
    'feminin' => 'e',
    'element' => 'Thème',
    'fiche' => 1,
    'type_element' => 'maquette',
    'element_pluriel' => 'Thèmes',
    'nom_table_sql' => "maquette",
    'affichage_recherche' => "#nom_application#",
    'affichage_fiche_type' => "#nom_application#",
    'affichage_dans_liste' => "#nom_application#",
    'affichage_pour_select' => "#nom_application#",
    'parametre' => 1,
  ),
  'champs_libres' =>
  array (
    'nom_application' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Nom de l\'application',
      'nom_sql' => 'nom_application',
      'recherche' => 1,
    ),
    'logo_application' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Logo de l\'application',
      'nom_sql' => 'logo_application',
      'type' => 7,
    ),
    'utilisation_logo_haut_gauche' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Utilisation du logo en haut à gauche',
      'nom_sql' => 'utilisation_logo_haut_gauche',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'devise_application_symbole' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Devise de l\'application - Symbole',
      'nom_sql' => 'devise_application_symbole',
    ),
    'page_accueil' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Page d\'accueil',
      'nom_sql' => 'page_accueil',
      'valeur_defaut' => '/eden/accueil',
    ),
    'devise_application_iso' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Devise de l\'application - ISO',
      'nom_sql' => 'devise_application_iso',
    ),
    'devise_application_nom' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Devise de l\'application - Nom',
      'nom_sql' => 'devise_application_nom',
    ),
    'taille_police' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Taille de la police (en %)',
      'nom_sql' => 'taille_police',
      'type' => 2,
    ),
    'poids_police' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Poids de la police',
      'nom_sql' => 'poids_police',
    ),
    'logo_application_connexion' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Logo application - Page de connexion',
      'nom_sql' => 'logo_application_connexion',
      'type' => 7,
    ),
    'langue_par_defaut' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Langue par défaut',
      'nom_sql' => 'langue_par_defaut',
      'type' => 42,
      'type_element_ajax' => 'traduction_langue',
    ),
    'par_defaut' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Maquette par défaut',
      'nom_sql' => 'par_defaut',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'favicon' =>
    array (
      'type_element' => 'maquette',
      'nom' => 'Favicon',
      'nom_sql' => 'favicon',
      'type' => 7,
    ),
  ),
);
