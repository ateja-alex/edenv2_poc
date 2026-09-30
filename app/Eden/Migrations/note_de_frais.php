<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Note de frais',
    'element' => 'note de frais',
    'type_element' => 'note_de_frais',
    'element_pluriel' => 'Notes de frais',
    'fiche' => 1,
  ),
  'champs_libres' => 
  array (
    'utilisateur_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Nom',
      'nom_sql' => 'utilisateur_id',
      'type' => 42,
      'recherche' => 1,
      'modification_post_validation' => 1,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'index' => 1,
    ),
    'client_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'obligatoire' => 1,
      'modification_post_validation' => 1,
      'type_element_ajax' => 'client',
    ),
    'entite_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'projet_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'recherche' => 1,
      'obligatoire' => 1,
      'modification_post_validation' => 1,
      'type_element_ajax' => 'projet',
    ),
    'date' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Salarié',
      'nom_sql' => 'date',
      'type' => 4,
      'modifier_en_masse' => 1,
      'modification_post_validation' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'index' => 1,
    ),
    'montant_ht' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Montant HT',
      'nom_sql' => 'montant_ht',
      'type' => 3,
      'lecture_seule' => 1,
    ),
    'montant_ttc' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Montant TTC',
      'nom_sql' => 'montant_ttc',
      'type' => 3,
      'recherche' => 1,
      'lecture_seule' => 1,
      'index' => 1,
    ),
    'scan' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Justificatif',
      'nom_sql' => 'scan',
      'type' => 7,
      'obligatoire' => 1,
    ),
    'accepte' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Acceptée',
      'nom_sql' => 'accepte',
      'type' => 20,
      'liste_choix' => 3,
    ),
    'rembourse' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Remboursée',
      'nom_sql' => 'rembourse',
      'type' => 20,
      'liste_choix' => 14,
      'modifier_en_masse' => 1,
      'modification_post_validation' => 1,
    ),
    'refacturable' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Refacturable',
      'nom_sql' => 'refacturable',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'comptabilisee' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Comptabilisée',
      'nom_sql' => 'comptabilisee',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'statut' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 301,
    ),
    'commentaire' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Commentaire',
      'nom_sql' => 'commentaire',
      'type' => 6,
    ),
    'montant_rembourse' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Montant remboursé',
      'nom_sql' => 'montant_rembourse',
      'type' => 3,
      'recherche' => 1,
      'lecture_seule' => 1,
      'modification_post_validation' => 1,
    ),
    'article_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Catégorie de dépenses',
      'nom_sql' => 'article_id',
      'type' => 42,
      'modification_post_validation' => 1,
      'type_element_ajax' => 'article_note_de_frais',
      'desactiver_creation_a_la_volee' => 1,
    ),
    'doublon' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Doublon',
      'nom_sql' => 'doublon',
      'type' => 1,
    ),
    'date_previ_remboursement' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Date prévi. remboursement',
      'nom_sql' => 'date_previ_remboursement',
      'type' => 4,
      'lecture_seule' => 1,
    ),
    'date_reelle_de_remboursement' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Date réelle de remboursement',
      'nom_sql' => 'date_reelle_de_remboursement',
      'type' => 4,
      'modifier_en_masse' => 1,
      'modification_post_validation' => 1,
    ),
    'total_tva' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Total TVA',
      'format_champ' => 'monetaire',
      'nom_sql' => 'total_tva',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'cb_entreprise' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'CB Entreprise',
      'format_champ' => 'toggle',
      'nom_sql' => 'cb_entreprise',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'responsable_id' => 
    array (
      'type_element' => 'note_de_frais',
      'nom' => 'Responsable',
      'nom_sql' => 'responsable_id',
      'type' => 42,
      'modifier_en_masse' => 1,
      'type_element_ajax' => 'utilisateur',
    ),
    'valide_n1' =>
    array(
      'nom' => "Validé par le N+1",
      'type' => 20,
      'liste_choix' => 3,
    ),
    'valide_n2' =>
    array(
      'nom' => "Validé par le N+2",
      'type' => 20,
      'liste_choix' => 3,
    ),
    'devise' => 
      array (
        'nom' => 'Devise',
        'type' => 20,
        'liste_choix' => 25,
      ),
    'taux_de_change' => 
      array (
        'nom' => 'Taux de change',
        'type' => 3,
      ),
    'heure' =>
      array(
        'nom' => 'Heure',
        'type' => 8,
        'format_champ' => 'H:i'
      ),
      'doublon_potentiel' => 
      array(
        'nom' => 'Doublon potentiel',
        'type' => 20,
        'liste_choix' => 14,
        'lecture_seule' => 1,
        'modification_post_validation' => 1,
      ),
      'ecart_gestion_ttc' => [
				'nom' => "Écart de gestion TTC",
				'type' => 3,
			],
  ),
);