<?php
 return array (
  'table_libre' =>
  array (
    'nom_table' => 'Adresses',
    'nom_table_sql' => 'adresse',
    'feminin' => 'e',
    'element' => 'adresse',
    'type_element' => 'adresse',
    'element_pluriel' => 'adresses',
    'affichage_recherche' => '#adresse# #code_postal# #ville# ',
    'affichage_fiche_type' => '#adresse# #code_postal# #ville# ',
    'affichage_dans_liste' => '#adresse# #code_postal# #ville# ',
    'affichage_pour_select' => '#adresse# #code_postal# #ville# ',
  ),
  'champs_libres' =>
  array (
    'societe' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Société',
      'nom_sql' => 'societe',
      'recherche' => 1,
    ),
    'adresse' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Adresse',
      'nom_sql' => 'adresse',
      'recherche' => 1,
    ),
    'adresse_complement' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Complément',
      'nom_sql' => 'adresse_complement',
      'recherche' => 1,
    ),
    'client_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'contact_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Contact',
      'nom_sql' => 'contact_id',
      'type' => 42,
      'type_element_ajax' => 'contact',
    ),
    'fournisseur_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Fournisseur',
      'nom_sql' => 'fournisseur_id',
      'type' => 42,
      'type_element_ajax' => 'fournisseur',
    ),
    'projet_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'type_element_ajax' => 'projet',
    ),
    'ville' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Ville',
      'format_champ' => 'majuscule',
      'nom_sql' => 'ville',
      'recherche' => 1,
    ),
    'code_postal' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Code postal',
      'nom_sql' => 'code_postal',
      'recherche' => 1,
      'format_champ' => 'code_postal',
      'contenu' => '{"region":"region","ville":"ville"}'
    ),
    'region' =>
      array (
          'type_element' => 'adresse',
          'nom' => 'Région',
          'format_champ' => 'majuscule',
          'nom_sql' => 'region'
      ),
    'nom_adresse' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Nom adresse',
      'nom_sql' => 'nom_adresse',
    ),
    'nom' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
    ),
    'prenom' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Prénom',
      'nom_sql' => 'prenom',
    ),
    'pays_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Pays',
      'nom_sql' => 'pays_id',
      'type' => 20,
      'liste_choix' => 28,
    ),
    'type_adresse' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Type adresse',
      'nom_sql' => 'type_adresse',
      'type' => 20,
      'liste_choix' => 31,
      'type_element_ajax' => 'adresse',
    ),
    'telephone_portable' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Téléphone portable',
      'nom_sql' => 'telephone_portable',
    ),
    'telephone_fixe' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Téléphone fixe',
      'nom_sql' => 'telephone_fixe',
    ),
    'message_enregistrement_adresse' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'message_enregistrement_adresse',
      'nom_sql' => 'message_enregistrement_adresse',
    ),
    'entite_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'latitude' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Latitude',
      'nom_sql' => 'latitude',
    ),
    'longitude' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Longitude',
      'nom_sql' => 'longitude',
    ),
    'type_element' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Type element',
      'nom_sql' => 'type_element',
    ),
    'element_id' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Element',
      'nom_sql' => 'element_id',
      'type' => 2,
    ),
    'adresse_par_defaut' =>
    array (
      'type_element' => 'adresse',
      'nom' => 'Adresse par défaut',
      'format_champ' => 'toggle',
      'nom_sql' => 'adresse_par_defaut',
      'type' => 20,
      'liste_choix' => 14,
    ),
  ),
);
