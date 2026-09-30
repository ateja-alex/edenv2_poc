<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Factures Vente et Acomptes',
    'nom_table_sql' => 'union_facture_vente_acompte_vente',
    'element' => 'Facture Vente et Acompte',
    'type_element' => 'union_facture_vente_acompte_vente',
    'element_pluriel' => 'Factures Vente et Acomptes',
    'affichage_dans_liste' => '#reference_document#',
    'editable_client' => 1,
    'vue_sql' => 1,
  ),
  'champs_libres' => 
  array (
    'date' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date',
    ),
    'client_id' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'client_id',
    ),
    'reference_document' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'reference_document',
    ),
    'objet' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'objet',
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Montant TTC',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_ttc',
      'nombre_decimale' => 2,
    ),
    'solde_document_ttc' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Solde TTC',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'solde_document_ttc',
    ),
    'regle' => 
    array (
      'type_element' => 'union_facture_vente_acompte_vente',
      'nom' => 'Réglé',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'regle',
    ),
  ),
);