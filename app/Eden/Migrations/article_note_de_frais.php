<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Article note de frais',
    'nom_table_sql' => 'article_note_de_frais',
    'element' => 'Article note de frais',
    'type_element' => 'article_note_de_frais',
    'element_pluriel' => 'Articles note de frais',
    'affichage_dans_liste' => '#nom#',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'code_tva' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Code de tva',
      'nom_sql' => 'code_tva',
      'type' => 42,
      'type_element_ajax' => "code_tva",
      'format_champ' => 'select',
      'obligatoire' => 1,
    ),
    'plafond' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Plafond',
      'nom_sql' => 'plafond',
      'type' => 3,
    ),
    'remboursement_plafonne' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Remboursement plafonné',
      'nom_sql' => 'remboursement_plafonne',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'compte_comptable' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Compte comptable',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'compte_comptable',
    ),
    'categorie_depense_mindee' => 
    array (
      'type_element' => 'article_note_de_frais',
      'nom' => 'Catégorie dépense MINDEE',
      'nom_sql' => 'categorie_depense_mindee',
      'type' => 20,
      'liste_choix' => 615,
    ),
  ),
);