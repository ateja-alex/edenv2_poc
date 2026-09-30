<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Tableaux de bord',
    'nom_table_sql' => 'tableau_de_bord',
    'feminin' => 'e',
    'element' => 'tableau de bord',
    'type_element' => 'tableau_de_bord',
    'element_pluriel' => 'tableaux de bord',
    'gestion_droits' => 1,
    'module' => 'Gestion commerciale',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'description' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Description',
      'nom_sql' => 'description',
      'type' => 6,
    ),
    'filtres' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Filtres',
      'nom_sql' => 'filtres',
      'type' => 6,
    ),
    'type' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Type',
      'nom_sql' => 'type',
      'type' => 20,
      'liste_choix' => 312,
    ),
    'disponible_extranet' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Disponible sur l\'extranet',
      'nom_sql' => 'disponible_extranet',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'ordre' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Ordre',
      'nom_sql' => 'ordre',
      'type' => 2,
    ),
    'entite_id' => 
    array (
      'type_element' => 'tableau_de_bord',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
  ),
);