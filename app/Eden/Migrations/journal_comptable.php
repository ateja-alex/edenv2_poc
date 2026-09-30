<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Journaux comptables',
    'nom_table_sql' => 'journal_comptable',
    'element' => 'Journal',
    'type_element' => 'journal_comptable',
    'element_pluriel' => 'Journaux',
    'affichage_recherche' => '#code_journal#  #nom# ',
    'affichage_fiche_type' => '#code_journal#  #nom# ',
    'affichage_dans_liste' => '#code_journal#  #nom# ',
    'affichage_pour_select' => '#code_journal#  #nom# ',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'journal_comptable',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'code_journal' => 
    array (
      'type_element' => 'journal_comptable',
      'nom' => 'Code',
      'nom_sql' => 'code_journal',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'compte_contrepartie' => 
    array (
      'type_element' => 'journal_comptable',
      'nom' => 'Compte contrepartie',
      'nom_sql' => 'compte_contrepartie',
      'obligatoire' => 1,
    ),
  ),
);