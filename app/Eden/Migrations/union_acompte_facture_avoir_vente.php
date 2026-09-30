<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'union_acompte_facture_avoir_vente',
    'nom_table_sql' => 'union_acompte_facture_avoir_vente',
    'element' => 'union_acompte_facture_avoir_vente',
    'type_element' => 'union_acompte_facture_avoir_vente',
    'element_pluriel' => 'union_acompte_facture_avoir_vente',
    'affichage_dans_liste' => '#reference_document#',
  ),
  'champs_libres' => 
  array (
    'id_document' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Id document',
      'nom_sql' => 'id_document',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'lien_interface_paiement_payline',
    ),
    'date' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Mode de paiement',
      'nom_sql' => 'mode_paiement_id',
      'type' => 20,
      'liste_choix' => 7,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'mode_paiement_id',
    ),
    'projet_id' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'reference_document',
    ),
    'date_de_reglement' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Date règlement',
      'nom_sql' => 'date_de_reglement',
      'type' => 4,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date_de_reglement',
    ),
    'objet' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'objet',
    ),
    'entite_id' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'entite_id',
    ),
    'client_id' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Solde document ttc',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'solde_document_ttc',
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'frais_de_livraison',
    ),
    'comptabilise' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Comptabilisée',
      'nom_sql' => 'comptabilise',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'comptabilise',
    ),
    'valide' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Validée',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'valide',
    ),
    'regle' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Réglée',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'regle',
    ),
    'canal' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'canal',
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Client livré',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'client_livraison_id',
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_livraison',
    ),
    'adresse_de_facturation' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Adresse de facturation',
      'nom_sql' => 'adresse_de_facturation',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_facturation',
    ),
    'remise_globale' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale',
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Remise globale (type)',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale_type',
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
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
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 104,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'statut',
    ),
    'marge' => 
    array (
      'type_element' => 'union_acompte_facture_avoir_vente',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'marge',
    ),
  ),
);