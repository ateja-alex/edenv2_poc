<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Fournisseurs',
    'nom_table_sql' => 'fournisseur',
    'element' => 'Fournisseur',
    'type_element' => 'fournisseur',
    'element_pluriel' => 'Fournisseurs',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'affichage_recherche' => '#raison_sociale# ',
    'affichage_fiche_type' => '#raison_sociale# ',
    'affichage_dans_liste' => '#raison_sociale#',
    'affichage_pour_select' => '#raison_sociale# ',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-truck',
  ),
  'champs_libres' => 
  array (
    'raison_sociale' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Raison sociale',
      'format_champ' => 'majuscule',
      'nom_sql' => 'raison_sociale',
      'recherche' => 1,
    ),
    'nom' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
    ),
    'adresse' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Adresse',
      'nom_sql' => 'adresse',
    ),
    'adresse_complement' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Complément',
      'nom_sql' => 'adresse_complement',
    ),
    'code_postal' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Code postal',
      'nom_sql' => 'code_postal',
    ),
    'ville' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Ville',
      'nom_sql' => 'ville',
    ),
    'telephone' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Téléphone',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone',
    ),
    'adresse_email' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Adresse email',
      'format_champ' => 'email',
      'nom_sql' => 'adresse_email',
    ),
    'pays' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Pays',
      'nom_sql' => 'pays',
      'type' => 1,
    ),
    'entite_id' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'commentaires' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Commentaires',
      'nom_sql' => 'commentaires',
      'type' => 6,
    ),
    'forcer_tva_0' => 
    array (
      'type_element' => 'fournisseur',
      'nom_sql' => 'forcer_tva_0',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'compte_auxiliaire' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Compte auxiliaire',
      'nom_sql' => 'compte_auxiliaire',
    ),
    'lien_interface_fournisseur' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Lien vers interface fournisseur',
      'nom_sql' => 'lien_interface_fournisseur',
    ),
    'clef_interface_fournisseur' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Lien vers interface fournisseur',
      'nom_sql' => 'clef_interface_fournisseur',
    ),
    'siren' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'SIREN',
      'format_champ' => 'siren',
      'nom_sql' => 'siren',
    ),
    'rib' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'RIB',
      'nom_sql' => 'rib',
      'type' => 7,
    ),
    'utiliser_references_fournisseur' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Utiliser les codes articles du fournisseur',
      'nom_sql' => 'utiliser_references_fournisseur',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'categorie_comptable_id' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Catégorie comptable',
      'nom_sql' => 'categorie_comptable_id',
      'type' => 42,
      'type_element_ajax' => 'categorie_comptable',
    ),
    'date_derniere_synchro_send_in_blue' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Date de dernière synchro Send In Blue',
      'nom_sql' => 'date_derniere_synchro_send_in_blue',
      'type' => 5,
    ),
    'modele_document_defaut_devis_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. devis',
      'nom_sql' => 'modele_document_defaut_devis_achat',
      'type' => 20,
      'liste_choix' => 91,
    ),
    'modele_document_defaut_commande_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. commande',
      'nom_sql' => 'modele_document_defaut_commande_achat',
      'type' => 20,
      'liste_choix' => 90,
    ),
    'modele_document_defaut_bl_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. BL',
      'nom_sql' => 'modele_document_defaut_bl_achat',
      'type' => 20,
      'liste_choix' => 89,
    ),
    'modele_document_defaut_acompte_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. acompte',
      'nom_sql' => 'modele_document_defaut_acompte_achat',
      'type' => 20,
      'liste_choix' => 190,
    ),
    'modele_document_defaut_facture_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. facture',
      'nom_sql' => 'modele_document_defaut_facture_achat',
      'type' => 20,
      'liste_choix' => 92,
    ),
    'modele_document_defaut_avoir_achat' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Modèle de doc. avoir',
      'nom_sql' => 'modele_document_defaut_avoir_achat',
      'type' => 20,
      'liste_choix' => 88,
    ),
    'numero_de_tva' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'Numéro de TVA',
      'nom_sql' => 'numero_de_tva',
    ),
    'siret' => 
    array (
      'type_element' => 'fournisseur',
      'nom' => 'SIRET',
      'format_champ' => 'siret',
      'nom_sql' => 'siret',
    ),
    'catalogue_groupement_id' => array(
      'nom' => "Catalogue groupement",
      'type' => 42,
      'type_element_ajax' => 'catalogue_groupement',
      'filtres' => [
        'catalogue_groupement' => array (
          array (
            'operateur' => 0,
            'exclu' => 0,
            'blocs' => array (),
            'filtres' => 
              array (
                array (
                  'type_element' => 'catalogue_groupement',
                  'champ_liaison' => NULL,
                  'valeurs' => 'lien_champ|fournisseur.entite_id',
                  'nom_sql' => 'entite_id',
                  'operateur' => 0,
                ),
                array (
                  'type_element' => 'catalogue_groupement',
                  'champ_liaison' => NULL,
                  'valeurs' => array(0),
                  'nom_sql' => 'archive',
                  'operateur' => 0,
                ),
              ),
          ),
        ), 
      ],
    )
  ),
);