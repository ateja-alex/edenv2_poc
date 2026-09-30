<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Exercices comptables',
    'nom_table_sql' => 'exercice',
    'element' => 'Exercice comptable',
    'type_element' => 'exercice',
    'element_pluriel' => 'Exercices comptables',
    'parametre' => 1,
    'affichage_recherche' => '#nom# ',
    'affichage_fiche_type' => '#nom# ',
    'affichage_dans_liste' => '#nom# ',
    'affichage_pour_select' => '#nom# ',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'exercice',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'debut' => 
    array (
      'type_element' => 'exercice',
      'nom' => 'Début',
      'nom_sql' => 'debut',
      'type' => 4,
      'obligatoire' => 1,
    ),
    'fin' => 
    array (
      'type_element' => 'exercice',
      'nom' => 'Fin',
      'nom_sql' => 'fin',
      'type' => 4,
      'obligatoire' => 1,
    ),
  ),
);