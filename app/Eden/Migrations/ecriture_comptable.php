<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Ecritures comptables',
    'nom_table_sql' => 'ecriture_comptable',
    'element' => 'Ecriture comptable',
    'type_element' => 'ecriture_comptable',
    'element_pluriel' => 'Ecritures comptables',
  ),
  'champs_libres' => 
  array (
    'ecriture_id' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Ecriture #',
      'nom_sql' => 'ecriture_id',
      'type' => 2,
      'obligatoire' => 1,
    ),
    'date' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'obligatoire' => 1,
    ),
    'journal_id' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Journal',
      'nom_sql' => 'journal_id',
      'type' => 42,
      'type_element_ajax' => 'journal_comptable',
      'obligatoire' => 1,
    ),
    'compte_comptable_id' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Compte',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'compte_comptable',
    ),
    'auxiliaire' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Auxiliaire',
      'nom_sql' => 'auxiliaire',
    ),
    'debit' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Débit',
      'nom_sql' => 'debit',
      'type' => 3,
    ),
    'credit' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Crédit',
      'nom_sql' => 'credit',
      'type' => 3,
    ),
    'type_element' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'type_element',
      'nom_sql' => 'type_element',
    ),
    'element_id' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'element_id',
      'nom_sql' => 'element_id',
      'type' => 2,
    ),
    'entite_id' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'entite',
    ),
    'exporte' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Exportée',
      'nom_sql' => 'exporte',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'libelle' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Libellé',
      'nom_sql' => 'libelle',
    ),
    'reference' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Référence',
      'nom_sql' => 'reference',
    ),
    'sens' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Sens',
      'nom_sql' => 'sens',
    ),
    'integration' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Intégration',
      'nom_sql' => 'integration',
      'type' => 1,
    ),
    'montant' => 
    array (
      'type_element' => 'ecriture_comptable',
      'nom' => 'Montant',
      'nom_sql' => 'montant',
      'type' => 3,
    ),
  ),
);