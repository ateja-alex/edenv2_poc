<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'union_facture_avoir_negatif',
    'nom_table_sql' => 'union_facture_avoir_negatif',
    'element' => 'union_facture_avoir_negatif',
    'type_element' => 'union_facture_avoir_negatif',
    'element_pluriel' => 'union_facture_avoir_negatif',
    'affichage_dans_liste' => '#reference_document#',
    'vue_sql' => 1,
  ),
  'champs_libres' => 
  array (
    'type_document' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Type document',
      'nom_sql' => 'type_document',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'lien_interface_paiement_payline',
    ),
    'date' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date',
    ),
    'mode_paiement_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Mode de paiement',
      'nom_sql' => 'mode_paiement_id',
      'type' => 20,
      'liste_choix' => 7,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'mode_paiement_id',
    ),
    'projet_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'projet',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'projet_id',
    ),
    'reference_document' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'reference_document',
    ),
    'date_de_reglement' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Date règlement',
      'nom_sql' => 'date_de_reglement',
      'type' => 4,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date_de_reglement',
    ),
    'objet' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'objet',
    ),
    'entite_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'entite_id',
    ),
    'client_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'client_id',
    ),
    'montant_document_ht' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'HT',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ht',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_ht',
      'nombre_decimale' => 2,
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'TTC',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_ttc',
      'nombre_decimale' => 2,
    ),
    'montant_document_tva' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'TVA',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_tva',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_tva',
      'nombre_decimale' => 2,
    ),
    'solde_document_ttc' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Solde TTC',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'solde_document_ttc',
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'frais_de_livraison',
    ),
    'comptabilise' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Comptabilisée',
      'nom_sql' => 'comptabilise',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'comptabilise',
    ),
    'valide' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Validée',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'valide',
    ),
    'regle' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Réglée',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'regle',
    ),
    'canal' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'canal',
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Client livré',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'client_livraison_id',
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_livraison',
    ),
    'adresse_de_facturation' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Adresse de facturation',
      'nom_sql' => 'adresse_de_facturation',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_facturation',
    ),
    'remise_globale' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale',
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Remise globale (type)',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale_type',
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial_id',
      'type' => 42,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => 1,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'responsable_commercial_id',
    ),
    'statut' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 104,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'statut',
    ),
    'marge' => 
    array (
      'type_element' => 'union_facture_avoir_negatif',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'marge',
    ),
  ),
);