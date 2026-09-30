<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Echanges',
    'nom_table_sql' => 'echange',
    'element' => 'echange',
    'type_element' => 'echange',
    'element_pluriel' => 'echanges',
    'editable_client' => 1,
    'affichage_recherche' => '#date# #type# #utilisateur_id#',
    'affichage_fiche_type' => '#date# #type# #utilisateur_id#',
    'affichage_dans_liste' => '#date# #type# #utilisateur_id#',
    'affichage_pour_select' => '#date# #type# #utilisateur_id#',
  ),
  'champs_libres' => 
  array (
    'client_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'contact_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Contact',
      'nom_sql' => 'contact_id',
      'type' => 42,
      'type_element_ajax' => 'contact',
      'conditions_v_if_manuelle' => 'echange.type_element != \'contact\'',
    ),
    'fournisseur_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Fournisseur',
      'nom_sql' => 'fournisseur_id',
      'type' => 42,
      'type_element_ajax' => 'fournisseur',
      'conditions_v_if_manuelle' => 'echange.type_element != \'fournisseur\'',
    ),
    'lead_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Lead',
      'nom_sql' => 'lead_id',
      'type' => 42,
      'type_element_ajax' => 'lead',
      'conditions_v_if_manuelle' => 'echange.type_element != \'lead\'',
    ),
    'entite_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'utilisateur_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Utilisateur',
      'nom_sql' => 'utilisateur_id',
      'type' => 42,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
    ),
    'date' => 
    array (
      'type_element' => 'echange',
      'nom' => 'date',
      'nom_sql' => 'date',
      'type' => 5,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
    ),
    'type' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Type',
      'nom_sql' => 'type',
      'type' => 20,
      'liste_choix' => 33,
    ),
    'retour_negatif' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Retour négatif',
      'nom_sql' => 'retour_negatif',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'description' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Description',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'description',
      'type' => 6,
    ),
    'projet_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'type_element_ajax' => 'projet',
      'conditions_v_if_manuelle' => 'echange.type_element != \'projet\'',
    ),
    'retour_traite' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Retour traité',
      'nom_sql' => 'retour_traite',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'type_element' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Type élement',
      'nom_sql' => 'type_element',
      'type' => 21,
      'contenu' => '[{"type_element":"client","valeur":true},{"type_element":"fournisseur","valeur":true},{"type_element":"lead","valeur":true},{"type_element":"projet","valeur":true},{"type_element":"contact","valeur":true}]',
    ),
    'element_id' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Element ID',
      'nom_sql' => 'element_id',
      'type' => 22,
      'contenu' => 'type_element',
    ),
    'document' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Document',
      'nom_sql' => 'document',
      'type' => 7,
    ),
    'objet' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
    ),
    'emplacement' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Emplacement',
      'nom_sql' => 'emplacement',
    ),
    'date_fin' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Date de fin',
      'nom_sql' => 'date_fin',
      'type' => 5,
    ),
    'destinataires_email' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Destinataires de l\'email',
      'nom_sql' => 'destinataires_email',
      'type' => 6,
    ),
    'emetteur_email' => 
    array (
      'type_element' => 'echange',
      'nom' => 'Emetteur de l\'email',
      'nom_sql' => 'emetteur_email',
    ),
  ),
);