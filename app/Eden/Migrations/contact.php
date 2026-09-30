<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Contacts',
    'nom_table_sql' => 'contact',
    'element' => 'Contact',
    'type_element' => 'contact',
    'element_pluriel' => 'Contacts',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'affichage_recherche' => '#nom# #prenom# #fonction# #adresse_email# ',
    'affichage_fiche_type' => '#nom# #prenom# #fonction# #adresse_email# ',
    'affichage_dans_liste' => '#nom#, #prenom#, #adresse_email#',
    'affichage_pour_select' => '#nom# #prenom# #fonction# #adresse_email# ',
    'editable_client' => 1,
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'prenom' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Prénom',
      'nom_sql' => 'prenom',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'adresse_email' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Adresse email',
      'format_champ' => 'email',
      'nom_sql' => 'adresse_email',
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'telephone' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Téléphone',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone',
      'afficher_sur_formulaire' => 1,
    ),
    'telephone_portable' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Portable',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone_portable',
    ),
    'poste' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Poste',
      'nom_sql' => 'poste',
      'afficher_sur_formulaire' => 1,
    ),
    'contact_prioritaire' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Contact prioritaire',
      'format_champ' => 'toggle',
      'nom_sql' => 'contact_prioritaire',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'npai' => 
    array (
      'type_element' => 'contact',
      'nom' => 'npai',
      'nom_sql' => 'npai',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'client_id' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'fournisseur_id' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Fournisseur',
      'nom_sql' => 'fournisseur_id',
      'type' => 42,
      'type_element_ajax' => 'fournisseur',
    ),
    'entite_id' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'logo' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Logo',
      'format_champ' => 'logo',
      'nom_sql' => 'logo',
      'type' => 7,
      'type_fichier' => 'logo',
    ),
    'linkedin' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Linkedin',
      'format_champ' => 'url',
      'nom_sql' => 'linkedin',
      'contenu' => '["fab fa-linkedin-in","#ffffff","#3c5898"]',
    ),
    'facebook' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Facebook',
      'format_champ' => 'url',
      'nom_sql' => 'facebook',
      'contenu' => '["fab fa-facebook-f","#ffffff","#3c5898"]',
    ),
    'maps' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Maps',
      'format_champ' => 'url',
      'nom_sql' => 'maps',
      'contenu' => '["fas fa-map-marker-alt","#ffffff","#f75d50"]',
    ),
    'societe_com' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Societe.com',
      'format_champ' => 'url',
      'nom_sql' => 'societe_com',
      'contenu' => '["fas fa-info","#ffffff","#008de4"]',
    ),
    'twitter' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Twitter',
      'format_champ' => 'url',
      'nom_sql' => 'twitter',
      'contenu' => '["fab fa-twitter","#ffffff","#1ca2f1"]',
    ),
    'date_derniere_synchro_send_in_blue' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Date de dernière synchro Send In Blue',
      'nom_sql' => 'date_derniere_synchro_send_in_blue',
      'type' => 5,
    ),
    'statut' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 581,
      'valeur_defaut' => '0',
    ),
    'civilite' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Civilité',
      'nom_sql' => 'civilite',
      'type' => 20,
      'liste_choix' => 39,
      'valeur_defaut' => '1',
    ),
    'fonction' => 
    array (
      'type_element' => 'contact',
      'nom' => 'Fonction',
      'nom_sql' => 'fonction',
      'type' => 1,
    ),
    'utilisateur_extranet_id' => 
    array (
      'nom' => 'Utilisateur extranet',
      'type' => 42,
      'type_element_ajax' => 'utilisateur_extranet',
    ),
  ),
);