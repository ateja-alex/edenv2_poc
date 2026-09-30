<?php
 return array (
  'table_libre' =>
  array (
    'nom_table' => 'Couleurs des thèmes',
    'feminin' => '',
    'element' => 'Couleurs des thèmes',
    'type_element' => 'maquette_couleurs',
    'element_pluriel' => 'Couleurs des thèmes',
    'nom_table_sql' => "maquette_couleurs",
    'affichage_recherche' => "#nom_couleur#",
    'affichage_fiche_type' => "#nom_couleur#",
    'affichage_dans_liste' => "#nom_couleur#",
    'affichage_pour_select' => "#nom_couleur#",
    'parametre' => 1,
  ),
  'champs_libres' =>
  array (
    'maquette' =>
    array (
      'nom' => 'Maquette',
      'type' => 42,
      'type_element_ajax' => 'maquette',
      'obligatoire' => 1, 
    ),
    'nom_couleur' =>
    array (
      'nom' => 'Nom de la couleur',
      'type' => 20,
      'liste_choix' => 2,
      'obligatoire' => 1, 
      'recherche' => 1, 
      'cacher_sans_valeur' => 1,
    ),
    'valeur' =>
    array (
      'nom' => 'Valeur de la couleur',
      'type' => 9,
    ),
    'valeurs' =>
    array (
      'nom' => 'Valeurs de la couleur',
      'type' => 10,
      'type_reference' => 9,
    ),
  ),
);
