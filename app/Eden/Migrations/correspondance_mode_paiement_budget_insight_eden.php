<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Correspondance mode de Paiement Rapprochement bancaire',
    'nom_table_sql' => 'correspondance_mode_paiement_budget_insight_eden',
    'feminin' => 'e',
    'element' => 'Correspondance mode de Paiement Rapprochement bancaire',
    'type_element' => 'correspondance_mode_paiement_budget_insight_eden',
    'element_pluriel' => 'Correspondance mode de Paiement Rapprochement bancaire',
    'module' => 'Comptabilité',
    'parametre' => 1,
    'icone_fontawesome' => 'fa-money-bill-wave-alt',
  ),
  'champs_libres' => 
  array (
    'mode_paiement_insight' => 
    array (
      'type_element' => 'correspondance_mode_paiement_budget_insight_eden',
      'nom' => 'Mode de paiement Budget Insight',
      'nom_sql' => 'mode_paiement_insight',
      'type' => 20,
      'liste_choix' => 502,
    ),
    'mode_paiement_eden' => 
    array (
      'type_element' => 'correspondance_mode_paiement_budget_insight_eden',
      'nom' => 'Mode de paiement Eden',
      'nom_sql' => 'mode_paiement_eden',
      'type' => 20,
      'liste_choix' => 7,
    ),
  ),
);