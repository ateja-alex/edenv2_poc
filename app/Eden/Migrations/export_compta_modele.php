<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Modèles d\'export comptable',
    'element' => 'modèle',
    'type_element' => 'export_compta_modele',
    'element_pluriel' => 'modèles',
    'fiche' => 1,
    'module' => 'Comptabilité',
    'affichage_dans_liste' => '#nom#',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'format_de_fichier' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Format de fichier',
      'nom_sql' => 'format_de_fichier',
      'type' => 20,
      'liste_choix' => 520,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'format_de_date' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Format de date',
      'nom_sql' => 'format_de_date',
      'type' => 20,
      'liste_choix' => 521,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'ligne_de_titre' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Inclure la ligne de titre',
      'nom_sql' => 'ligne_de_titre',
      'type' => 20,
      'liste_choix' => 14,
      'afficher_sur_formulaire' => 1,
    ),
    'entite_id' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'afficher_sur_formulaire' => 1,
      'type_element_ajax' => 'entite',
    ),
    'par_defaut' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Par défaut',
      'nom_sql' => 'par_defaut',
      'type' => 20,
      'liste_choix' => 14,
      'afficher_sur_formulaire' => 1,
    ),
    'afficher_ligne_titre' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Entête de colonne',
      'nom_sql' => 'afficher_ligne_titre',
      'type' => 20,
      'liste_choix' => 14,
      'afficher_sur_formulaire' => 1,
    ),
    'type_element' => 
    array (
      'type_element' => 'export_compta_modele',
      'nom' => 'Type élément',
      'nom_sql' => 'type_element',
      'type' => 21,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => 'ecriture_comptable',
      'contenu' => '[{"type_element":"ecriture_comptable","valeur":true}]',
    ),
  ),
);