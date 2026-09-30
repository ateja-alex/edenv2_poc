<?php
 return array (
  'table_libre' =>
  array (
    'nom_table' => 'Articles',
    'nom_table_sql' => 'article',
    'element' => 'article',
    'type_element' => 'article',
    'element_pluriel' => 'articles',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'module' => 'Gestion commerciale',
    'affichage_recherche' => '#code_article# #designation# ',
    'affichage_fiche_type' => '#code_article# #designation# ',
    'affichage_dans_liste' => '#code_article#, #designation#',
    'affichage_pour_select' => '#code_article# #designation# ',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-cart-arrow-down',
  ),
  'champs_libres' =>
  array (
    'famille_id' =>
    array (
      'type_element' => 'article',
      'nom' => 'Famille',
      'nom_sql' => 'famille_id',
      'type' => 42,
      'type_element_ajax' => "famille",
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'code_article' =>
    array (
      'type_element' => 'article',
      'nom' => 'Code article',
      'nom_sql' => 'code_article',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'designation' =>
    array (
      'type_element' => 'article',
      'nom' => 'Désignation',
      'nom_sql' => 'designation',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'tarif' =>
    array (
      'type_element' => 'article',
      'nom' => 'Tarif HT',
      'nom_sql' => 'tarif',
      'type' => 3,
    ),
    'prix_d_achat' =>
    array (
      'type_element' => 'article',
      'nom' => 'Prix d\'achat',
      'nom_sql' => 'prix_d_achat',
      'type' => 3,
    ),
    'marge_devises' =>
    array (
      'type_element' => 'article',
      'nom' => 'Marge (devises)',
      'nom_sql' => 'marge_devises',
      'type' => 3,
    ),
    'marge_pourcent' =>
    array (
      'type_element' => 'article',
      'nom' => 'marge (%)',
      'nom_sql' => 'marge_pourcent',
      'type' => 3,
    ),
    'taux_de_tva' =>
    array (
      'type_element' => 'article',
      'nom' => 'Taux de TVA',
      'nom_sql' => 'taux_de_tva',
      'type' => 3,
    ),
    'description' =>
    array (
      'type_element' => 'article',
      'nom' => 'Description',
      'nom_sql' => 'description',
      'type' => 6,
    ),
    'titre_seo' =>
    array (
      'type_element' => 'article',
      'nom' => 'Titre seo',
      'nom_sql' => 'titre_seo',
    ),
    'mots_cles_seo' =>
    array (
      'type_element' => 'article',
      'nom' => 'Mots clés seo',
      'nom_sql' => 'mots_cles_seo',
    ),
    'description_seo' =>
    array (
      'type_element' => 'article',
      'nom' => 'Description seo',
      'nom_sql' => 'description_seo',
      'type' => 6,
    ),
    'url' =>
    array (
      'type_element' => 'article',
      'nom' => 'URL',
      'nom_sql' => 'url',
    ),
    'description_courte' =>
    array (
      'type_element' => 'article',
      'nom' => 'Description courte',
      'nom_sql' => 'description_courte',
      'type' => 6,
    ),
    'description_longue' =>
    array (
      'type_element' => 'article',
      'nom' => 'Description longue',
      'nom_sql' => 'description_longue',
      'type' => 6,
    ),
    'promo' =>
    array (
      'type_element' => 'article',
      'nom' => 'Promo (%)',
      'nom_sql' => 'promo',
      'type' => 3,
    ),
    'article_unite' =>
    array (
      'type_element' => 'article',
      'nom' => 'Unité',
      'nom_sql' => 'article_unite',
      'type' => 20,
      'liste_choix' => 81,
    ),
    'stock_seuil_mini' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stock minimum',
      'nom_sql' => 'stock_seuil_mini',
      'type' => 3,
    ),
    'stock_seuil_alerte' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stock d\'alerte',
      'nom_sql' => 'stock_seuil_alerte',
      'type' => 3,
    ),
    'stock_actuel' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stock actuel',
      'nom_sql' => 'stock_actuel',
      'type' => 3,
    ),
    'stock_reserve' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stock réservé',
      'nom_sql' => 'stock_reserve',
      'type' => 3,
    ),
    'stock_a_recevoir' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stock à recevoir',
      'nom_sql' => 'stock_a_recevoir',
      'type' => 3,
    ),
    'stockable' =>
    array (
      'type_element' => 'article',
      'nom' => 'Stockable',
      'nom_sql' => 'stockable',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'en_ligne' =>
    array (
      'type_element' => 'article',
      'nom' => 'En ligne',
      'nom_sql' => 'en_ligne',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'compte_comptable_produits_france' =>
    array (
      'type_element' => 'article',
      'nom' => 'Compte comptable produit france',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable_produits_france',
      'type' => 42,
      'type_element_ajax' => 'compte_comptable',
    ),
    'compte_comptable_produits_ue' =>
    array (
      'type_element' => 'article',
      'nom' => 'Compte comptable produit UE',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable_produits_ue',
      'type' => 42,
      'type_element_ajax' => 'compte_comptable',
    ),
    'compte_comptable_produits_export' =>
    array (
      'type_element' => 'article',
      'nom' => 'Compte comptable produit export',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable_produits_export',
      'type' => 42,
      'type_element_ajax' => 'compte_comptable',
    ),
    'compte_comptable_achats_france' =>
    array (
      'type_element' => 'article',
      'nom' => 'Compte comptable achat france',
      'format_champ' => 'select',
      'nom_sql' => 'compte_comptable_achats_france',
      'type' => 42,
      'type_element_ajax' => 'compte_comptable',
    ),
    'ordre' =>
    array (
      'type_element' => 'article',
      'nom' => 'Ordre',
      'nom_sql' => 'ordre',
      'type' => 3,
    ),
    'type_declinaison' =>
    array (
      'type_element' => 'article',
      'nom' => 'Type declinaison',
      'nom_sql' => 'type_declinaison',
      'type' => 20,
      'liste_choix' => 58,
    ),
    'archive' =>
    array (
      'type_element' => 'article',
      'nom' => 'Archivé',
      'nom_sql' => 'archive',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'type_article' =>
    array (
      'type_element' => 'article',
      'nom' => 'Type article',
      'nom_sql' => 'type_article',
      'type' => 20,
      'liste_choix' => 62,
    ),
    'quantifiable' =>
    array (
      'type_element' => 'article',
      'nom' => 'Quantifiable',
      'nom_sql' => 'quantifiable',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'utilisable_que_sur_les_devis' =>
    array (
      'type_element' => 'article',
      'nom' => 'Utilisable que sur les devis',
      'nom_sql' => 'utilisable_que_sur_les_devis',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'obligatoire_sur_les_documents' =>
    array (
      'type_element' => 'article',
      'nom' => 'Obligatoire sur les documents',
      'nom_sql' => 'obligatoire_sur_les_documents',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'unite' =>
    array (
      'type_element' => 'article',
      'nom' => 'Unité',
      'nom_sql' => 'unite',
      'type' => 20,
      'liste_choix' => 81,
    ),
    'description_sur_document' =>
    array (
      'type_element' => 'article',
      'nom' => 'Description sur les documents',
      'nom_sql' => 'description_sur_document',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'poids' =>
    array (
      'type_element' => 'article',
      'nom' => 'Poids',
      'nom_sql' => 'poids',
      'type' => 3,
    ),
    'ne_pas_reapprovisionner' =>
    array (
      'type_element' => 'article',
      'nom' => 'Ne pas réapprovisionner',
      'nom_sql' => 'ne_pas_reapprovisionner',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'disponibilite' =>
    array (
      'type_element' => 'article',
      'nom' => 'Disponibilite',
      'nom_sql' => 'disponibilite',
    ),
    'logo' =>
    array (
      'type_element' => 'article',
      'nom' => 'Logo',
      'format_champ' => 'logo',
      'nom_sql' => 'logo',
      'type' => 7,
      'type_fichier' => 'logo',
    ),
    'disponible_pour_saisie' =>
    array (
      'type_element' => 'article',
      'nom' => 'Disponible pour saisie',
      'nom_sql' => 'disponible_pour_saisie',
      'type' => 20,
      'liste_choix' => 140,
    ),
    'type_numero_de_serie' =>
    array (
      'type_element' => 'article',
      'nom' => 'Type de numéro de série',
      'nom_sql' => 'type_numero_de_serie',
      'type' => 20,
      'liste_choix' => 143,
    ),
    'derniere_date_inventaire' =>
    array (
      'type_element' => 'article',
      'nom' => 'Dernière date inventaire',
      'nom_sql' => 'derniere_date_inventaire',
      'type' => 4,
    ),
    'nature_id' =>
    array (
      'type_element' => 'article',
      'nom' => 'Nature',
      'nom_sql' => 'nature_id',
      'type' => 20,
      'liste_choix' => 310,
    ),
    'modele_de_calculateur_id' =>
    array (
      'type_element' => 'article',
      'nom' => 'Modèle de calculateur',
      'nom_sql' => 'modele_de_calculateur_id',
      'type' => 42,
      'type_element_ajax' => 'modele_de_calculateur',
    ),
    'fournisseur_id' =>
    array (
      'type_element' => 'article',
      'nom' => 'Fournisseur',
      'nom_sql' => 'fournisseur_id',
      'type' => 42,
      'type_element_ajax' => 'fournisseur',
    ),
    'tarif_force' =>
    array (
      'type_element' => 'article',
      'nom' => 'Tarif forcé',
      'nom_sql' => 'tarif_force',
      'type' => 3,
    ),
    'categorie_eco_contribution_id' =>
    array (
      'type_element' => 'article',
      'nom' => 'Catégorie d\'éco-contribution',
      'nom_sql' => 'categorie_eco_contribution_id',
      'type' => 42,
      'type_element_ajax' => 'categorie_eco_contribution',
    ),
    'type_application_eco_contribution' =>
    array (
      'type_element' => 'article',
      'nom' => 'Type d\'application de l\'éco-contribution',
      'nom_sql' => 'type_application_eco_contribution',
      'type' => 20,
      'liste_choix' => 635,
    ),
    'quantite_unite_eco_contribution' =>
    array (
      'type_element' => 'article',
      'nom' => 'Quantité d\'unité de l\'éco-contribution',
      'format_champ' => 'monetaire',
      'nom_sql' => 'quantite_unite_eco_contribution',
      'type' => 3,
      'nombre_decimale' => 3,
    ),
    'articles_substitution' =>
    array (
      'nom' => 'Articles de substitution',
      'type' => 10,
      'type_reference' => 42,
      'type_element_ajax' => 'article'
    ),
  ),
);