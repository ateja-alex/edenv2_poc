<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Leads',
    'nom_table_sql' => 'lead',
    'element' => 'Lead',
    'type_element' => 'lead',
    'element_pluriel' => 'Leads',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'affichage_recherche' => '#entreprise#  - #nom#  #prenom# ',
    'affichage_fiche_type' => '#entreprise#  - #nom#  #prenom# ',
    'affichage_dans_liste' => '#entreprise#, #nom#, #prenom#',
    'affichage_pour_select' => '#entreprise#  - #nom#  #prenom# ',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-usd',
  ),
  'champs_libres' => 
  array (
    'entreprise' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Raison sociale',
      'nom_sql' => 'entreprise',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'nom' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'prenom' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Prénom',
      'nom_sql' => 'prenom',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'telephone' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Téléphone',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'adresse_email' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Adressse email',
      'format_champ' => 'email',
      'nom_sql' => 'adresse_email',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial_id',
      'type' => 42,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => 1,
    ),
    'commentaires' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Commentaires',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'commentaires',
      'type' => 6,
      'afficher_sur_formulaire' => 1,
    ),
    'source' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Source',
      'nom_sql' => 'source',
      'type' => 1,
      'valeur_defaut' => '17',
      'cacher_sans_valeur' => 1,
    ),
    'statut' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 44,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '0',
    ),
    'etape' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Etape',
      'nom_sql' => 'etape',
      'type' => 20,
      'liste_choix' => 43,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '1',
    ),
    'note' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Notation',
      'nom_sql' => 'note',
      'type' => 13,
    ),
    'entite_id' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'date_derniere_synchro_send_in_blue' => 
    array (
      'type_element' => 'lead',
      'nom' => 'Date de dernière synchro Send In Blue',
      'nom_sql' => 'date_derniere_synchro_send_in_blue',
      'type' => 5,
    ),
  ),
);