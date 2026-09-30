<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Facture vente lignes d\'éco-contribution',
    'nom_table_sql' => 'facture_vente_lignes_eco_contribution',
    'element' => 'facture vente ligne d\'éco-contribution',
    'type_element' => 'facture_vente_lignes_eco_contribution',
    'element_pluriel' => 'factures ventes lignes d\'éco-contribution',
    'vue_sql' => 1,
  ),
  'champs_libres' => 
  array (
    'document_id' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Document',
      'nom_sql' => 'document_id',
      'type' => 42,
      'type_element_ajax' => 'facture_vente',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'document_id',
    ),
    'reference_document' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Référence document',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'reference_document',
    ),
    'date_document' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Date du document',
      'nom_sql' => 'date_document',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date',
    ),
    'article_id' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Article',
      'nom_sql' => 'article_id',
      'type' => 42,
      'type_element_ajax' => 'article',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'article_id',
    ),
    'quantite' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Quantité',
      'nom_sql' => 'quantite',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'quantite',
    ),
    'tarif' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Tarif',
      'nom_sql' => 'tarif',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif',
    ),
    'eco_contribution_inclue' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Eco-contribution inclue',
      'nom_sql' => 'eco_contribution_inclue',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_eco_contribution',
    ),
    'eco_contribution_en_sus' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Eco-contribution en sus',
      'nom_sql' => 'eco_contribution_en_sus',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_eco_contribution',
    ),
    'quantite_unite' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Quantité unité',
      'format_champ' => 'monetaire',
      'nom_sql' => 'quantite_unite',
      'type' => 3,
      'type_element_origine' => 'article',
      'nom_sql_origine' => 'quantite_unite_eco_contribution',
      'nombre_decimale' => 3,
    ),
    'unite' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Unité',
      'nom_sql' => 'unite',
      'type_element_origine' => 'categorie_eco_contribution',
      'nom_sql_origine' => 'unite',
    ),
    'categorie_eco_contribution_id' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Catégorie d\'éco-contribution',
      'nom_sql' => 'categorie_eco_contribution_id',
      'type' => 42,
      'type_element_ajax' => 'categorie_eco_contribution',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'categorie_eco_contribution_id',
    ),
    'eco_organisme_id' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'Eco-organisme',
      'nom_sql' => 'eco_organisme_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'eco_organisme',
      'type_element_origine' => 'famille_eco_contribution',
      'nom_sql_origine' => 'eco_organisme_id',
    ),
    'tva' => 
    array (
      'type_element' => 'facture_vente_lignes_eco_contribution',
      'nom' => 'TVA',
      'nom_sql' => 'tva',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_eco_contribution',
    ),
  ),
);