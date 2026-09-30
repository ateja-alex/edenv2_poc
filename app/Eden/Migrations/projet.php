<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Projets',
    'nom_table_sql' => 'projet',
    'element' => 'Projet',
    'type_element' => 'projet',
    'element_pluriel' => 'Projets',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'module' => 'Gestion de projet',
    'affichage_recherche' => '#nom# ',
    'affichage_fiche_type' => '#nom# ',
    'affichage_dans_liste' => '#nom#',
    'affichage_pour_select' => '#nom# ',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-pencil',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Nom du projet',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'valeur' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Valeur',
      'nom_sql' => 'valeur',
      'type' => 2,
    ),
    'client_id' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'entite_id' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'statut' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 52,
    ),
    'numero_de_projet' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Numéro de projet',
      'format_champ' => '{break}{numero}',
      'nom_sql' => 'numero_de_projet',
      'type' => 14,
      'lecture_seule' => 1,
    ),
    'marge_brute' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge brute',
      'nom_sql' => 'marge_brute',
      'type' => 3,
    ),
    'marge_nette' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge nette',
      'nom_sql' => 'marge_nette',
      'type' => 3,
    ),
    'montant_achats' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Montant des achats sur le projet',
      'nom_sql' => 'montant_achats',
      'type' => 3,
    ),
    'montant_ca' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Montant du CA',
      'nom_sql' => 'montant_ca',
      'type' => 3,
    ),
    'cout_rh' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Coût des ressources humaines',
      'nom_sql' => 'cout_rh',
      'type' => 3,
    ),
    'marge_brute_devis' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge brute devis',
      'nom_sql' => 'marge_brute_devis',
      'type' => 3,
    ),
    'marge_nette_devis' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge nette devis',
      'nom_sql' => 'marge_nette_devis',
      'type' => 3,
    ),
    'marge_brute_commande' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge brute commande',
      'nom_sql' => 'marge_brute_commande',
      'type' => 3,
    ),
    'marge_nette_commande' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge nette commande',
      'nom_sql' => 'marge_nette_commande',
      'type' => 3,
    ),
    'marge_brute_facture' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge brute facture',
      'nom_sql' => 'marge_brute_facture',
      'type' => 3,
    ),
    'marge_nette_facture' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Marge nette facture',
      'nom_sql' => 'marge_nette_facture',
      'type' => 3,
    ),
    'temps_transfo_premier_devis' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Temps transo projet (devis)',
      'nom_sql' => 'temps_transfo_premier_devis',
      'type' => 3,
    ),
    'delai_reponse_client' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Délai de réponse client',
      'nom_sql' => 'delai_reponse_client',
      'type' => 3,
    ),
    'probabilite' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Probabilité',
      'nom_sql' => 'probabilite',
      'type' => 20,
      'liste_choix' => 99,
    ),
    'valeur_ponderee' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Valeur pondérée',
      'nom_sql' => 'valeur_ponderee',
      'type' => 3,
    ),
    'logo' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Logo',
      'format_champ' => 'logo',
      'nom_sql' => 'logo',
      'type' => 7,
      'type_fichier' => 'logo',
    ),
    'date' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
    ),
    'montant_regle' => 
    array (
      'type_element' => 'projet',
      'nom' => 'Montant réglé',
      'nom_sql' => 'montant_regle',
      'type' => 3,
      'donnee_calculee_depuis' => 'projet',
      'donnee_calculee_requete' => 'select sum(montant_document_ttc - solde_document_ttc) as resultat from union_acompte_facture_avoir_vente where projet_id=\'#id_cible#\'',
    ),
  ),
);