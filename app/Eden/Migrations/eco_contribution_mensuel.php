<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Eco-contribution mensuel',
    'nom_table_sql' => 'eco_contribution_mensuel',
    'element' => 'éco-contribution mensuel',
    'type_element' => 'eco_contribution_mensuel',
    'element_pluriel' => 'éco-contributions mensuel',
    'vue_sql' => 1,
  ),
  'champs_libres' => 
  array (
    'date' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date',
    ),
    'eco_organisme_id' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'Eco-organisme',
      'nom_sql' => 'eco_organisme_id',
      'type' => 42,
      'obligatoire' => 1,
      'type_element_ajax' => 'eco_organisme',
      'type_element_origine' => 'famille_eco_contribution',
      'nom_sql_origine' => 'eco_organisme_id',
    ),
    'categorie_eco_contribution_id' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'Catégorie d\'éco-contribution',
      'nom_sql' => 'categorie_eco_contribution_id',
      'type' => 42,
      'type_element_ajax' => 'categorie_eco_contribution',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'categorie_eco_contribution_id',
    ),
    'quantite_unite' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
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
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'Unité',
      'nom_sql' => 'unite',
      'type_element_origine' => 'categorie_eco_contribution',
      'nom_sql_origine' => 'unite',
    ),
    'eco_contribution' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'Eco-contribution',
      'nom_sql' => 'eco_contribution',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_eco_contribution',
    ),
    'tva' => 
    array (
      'type_element' => 'eco_contribution_mensuel',
      'nom' => 'TVA éco-contribution',
      'nom_sql' => 'tva',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_eco_contribution',
    ),
  ),
);