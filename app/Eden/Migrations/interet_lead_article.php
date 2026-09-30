<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Intérêt des leads pour les articles',
    'nom_table_sql' => 'interet_lead_article',
    'element' => 'intérêt',
    'type_element' => 'interet_lead_article',
    'element_pluriel' => 'intérêts',
  ),
  'champs_libres' => 
  array (
    'lead_id' => 
    array (
      'type_element' => 'interet_lead_article',
      'nom' => 'Lead',
      'nom_sql' => 'lead_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'lead',
    ),
    'article_id' => 
    array (
      'type_element' => 'interet_lead_article',
      'nom' => 'Article',
      'nom_sql' => 'article_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'article',
    ),
    'interet' => 
    array (
      'type_element' => 'interet_lead_article',
      'nom' => 'Intérêt',
      'nom_sql' => 'interet',
      'type' => 20,
      'liste_choix' => 45,
      'valeur_defaut' => '2',
    ),
  ),
);