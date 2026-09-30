<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Unités de vente',
    'nom_table_sql' => 'article_unite',
    'element' => 'unité',
    'type_element' => 'article_unite',
    'element_pluriel' => 'unités',
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
      'type_element' => 'article_unite',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
  ),
);