<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Familles',
    'nom_table_sql' => 'famille',
    'feminin' => 'e',
    'element' => 'famille',
    'type_element' => 'famille',
    'element_pluriel' => 'familles',
    'fiche' => 1,
    'module' => 'Gestion commerciale',
    'parametre' => 1,
    'affichage_dans_liste' => '#nom#',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-object-group',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'famille',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'parent_id' => 
    array (
      'type_element' => 'famille',
      'nom' => 'Famille mere',
      'nom_sql' => 'parent_id',
      'type' => 42,
      'type_element_ajax' => 'famille',
      'afficher_sur_formulaire' => 1,
    ),
    'ordre' => 
    array (
      'type_element' => 'famille',
      'nom' => 'Ordre',
      'nom_sql' => 'ordre',
      'type' => 3,
    ),
    'image' => 
    array (
      'type_element' => 'famille',
      'nom' => 'Image',
      'nom_sql' => 'image',
      'type' => 7,
    ),
  ),
);