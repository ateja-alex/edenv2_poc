<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'union_facture_ligne_avoir_ligne_negatif',
    'nom_table_sql' => 'union_facture_ligne_avoir_ligne_negatif',
    'element' => 'union_facture_ligne_avoir_ligne_negatif',
    'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
    'element_pluriel' => 'union_facture_ligne_avoir_ligne_negatif',
    'vue_sql' => 1,
  ),
  'champs_libres' => 
  array (
    'type_ligne' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Type ligne',
      'nom_sql' => 'type_ligne',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'lien_interface_paiement_payline',
    ),
    'article_id' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Article',
      'nom_sql' => 'article_id',
      'type' => 42,
      'type_element_ajax' => 'article',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'article_id',
    ),
    'quantite' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Quantité',
      'nom_sql' => 'quantite',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'quantite',
    ),
    'tarif' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif',
      'nom_sql' => 'tarif',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif',
    ),
    'remise' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Remise',
      'nom_sql' => 'remise',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'remise',
    ),
    'tva' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tva',
      'nom_sql' => 'tva',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tva',
    ),
    'type_tarif' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Type tarif',
      'nom_sql' => 'type_tarif',
      'type' => 2,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'type_tarif',
    ),
    'remise_globale_ligne' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale_ligne',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'remise_globale_ligne',
    ),
    'tarif_saisi' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif saisi',
      'nom_sql' => 'tarif_saisi',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_saisi',
    ),
    'prix_achat' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Prix achat',
      'nom_sql' => 'prix_achat',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'prix_achat',
    ),
    'unite' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Unité',
      'nom_sql' => 'unite',
      'type' => 42,
      'type_element_ajax' => 'article_unite',
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'unite',
    ),
    'tarif_apres_remise' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif après remise',
      'nom_sql' => 'tarif_apres_remise',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_apres_remise',
    ),
    'tarif_net' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif net',
      'nom_sql' => 'tarif_net',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_net',
    ),
    'coefficient' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Coefficient',
      'nom_sql' => 'coefficient',
      'type' => 6,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'coefficient',
    ),
    'coefficient_article' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Coefficient par article',
      'nom_sql' => 'coefficient_article',
      'type' => 6,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'coefficient_article',
    ),
    'coefficient_regroupement' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Coefficient par regroupement',
      'nom_sql' => 'coefficient_regroupement',
      'type' => 6,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'coefficient_regroupement',
    ),
    'total' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif total',
      'nom_sql' => 'total',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'total',
    ),
    'tarif_force' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Tarif forcé',
      'nom_sql' => 'tarif_force',
      'type' => 3,
      'type_element_origine' => 'facture_vente_lignes',
      'nom_sql_origine' => 'tarif_force',
    ),
    'date' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'date',
    ),
    'montant_document_ht' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Montant document ht',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ht',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_ht',
      'nombre_decimale' => 2,
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Montant document ttc',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_ttc',
      'nombre_decimale' => 2,
    ),
    'montant_document_tva' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Montant document tva',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_tva',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'montant_document_tva',
      'nombre_decimale' => 2,
    ),
    'solde_document_ttc' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Solde document ttc',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'solde_document_ttc',
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'frais_de_livraison',
    ),
    'comptabilise' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Comptabilise',
      'nom_sql' => 'comptabilise',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'comptabilise',
    ),
    'valide' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Valide',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'valide',
    ),
    'regle' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Regle',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'regle',
    ),
    'canal' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'canal',
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Client livraison id',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'client_livraison_id',
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_livraison',
    ),
    'adresse_de_facturation' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Adresse de facturation',
      'nom_sql' => 'adresse_de_facturation',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'adresse_de_facturation',
    ),
    'remise_globale' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale',
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Remise globale type',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'remise_globale_type',
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Responsable commercial id',
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
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 104,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'statut',
    ),
    'marge' => 
    array (
      'type_element' => 'union_facture_ligne_avoir_ligne_negatif',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
      'type_element_origine' => 'facture_vente',
      'nom_sql_origine' => 'marge',
    ),
  ),
);