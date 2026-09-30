<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Utilisateurs extranet',
    'nom_table_sql' => 'utilisateur_extranet',
    'element' => 'Utilisateur extranet',
    'type_element' => 'utilisateur_extranet',
    'element_pluriel' => 'Utilisateurs extranet',
    'affichage_recherche' => '#email#',
    'affichage_fiche_type' => '#email#',
    'affichage_dans_liste' => '#email#',
    'affichage_pour_select' => '#email#',
  ),
  'champs_libres' => 
  array (
    'email' => 
    array (
      'nom' => 'Email',
      'format_champ' => 'email',
      'obligatoire' => 1,
    ),
    'nom' => 
    array (
      'nom' => 'Nom',
    ),
    'prenom' => 
    array (
      'nom' => 'Prénom',
    ),
    'old_mot_de_passe' => 
    array (
      'nom' => 'Ancien mot de passe',
    ),
    'mot_de_passe' => 
    array (
      'nom' => 'Mot de passe',
    ),
    'profil' => 
    array (
      'nom' => 'Profil',
      'type' => 42,
      'type_element_ajax' => 'profil',
      'filtres' => [
        'profil' => array (
          array (
            'operateur' => 0,
            'exclu' => 0,
            'blocs' => array (),
            'filtres' => 
            array (
              array (
                'type_element' => 'profil',
                'champ_liaison' => NULL,
                'valeurs' => array (1),
                'nom_sql' => 'extranet',
                'operateur' => 0,
              ),
            ),
          ),
        ), 
      ],
    ),
    'langue' => [
        'nom' => 'Langue',
        'type' => 42,
        'type_element_ajax' => 'traduction_langue'
    ],
    'remember_token' => 
    array (
      'nom' => 'remember_token',
    ),
    'autorise_a_se_connecter' => 
    array (
      'nom' => 'Autorisé à se connecter',
      'format_champ' => 'toggle',
      'type' => 20,
      'liste_choix' => 14,
      'valeur_defaut' => 1,
    ),
    'admin' => 
    array (
      'nom' => 'Admin',
      'type' => 20,
      'liste_choix' => 14,
      'format_champ' => 'toggle',
    ),
    'derniere_connexion' => 
    array (
      'nom' => 'Derniere connexion',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'renouveler_mot_de_passe' => 
    array (
      'nom' => 'Renouveler le mot de passe',
      'type' => 20,
      'liste_choix' => 14,
      'format_champ' => 'toggle',
    ),
    'date_derniere_modification_mot_de_passe' => 
    array (
      'nom' => 'Date de dernière modification du mot de passe',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'double_facteur_authentification' => array(
      'nom' => 'Double facteur d\'authentification',
      'type' => 20,
      'liste_choix' => 720,
    ),
    'code_connexion' => array(
      'nom' => 'Code de connexion',
      'lecture_seule' => 1,
    ),
    'expiration_code_connexion' => array(
      'nom' => 'Expiration du code de connexion',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'google2fa_secret' => array(
      'nom' => 'Google 2FA Secret',
      'lecture_seule' => 1,
    ),
    'contact_selectionne_id' => array(
      'nom' => 'Contact sélectionné',
      'type' => 42,
      'type_element_ajax' => 'contact',
    ),
  ),
);