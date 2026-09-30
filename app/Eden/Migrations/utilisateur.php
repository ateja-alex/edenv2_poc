<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Utilisateurs',
    'nom_table_sql' => 'utilisateur',
    'element' => 'Utilisateur',
    'type_element' => 'utilisateur',
    'element_pluriel' => 'Utilisateurs',
    'affichage_recherche' => '  #nom# #prenom#  ',
    'affichage_fiche_type' => '#nom#  #prenom# ',
    'affichage_dans_liste' => '#nom# #prenom# ',
    'affichage_pour_select' => '#nom# #prenom# ',
  ),
  'champs_libres' => 
  array (
    'nom' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'prenom' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Prénom',
      'nom_sql' => 'prenom',
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'email' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Email',
      'nom_sql' => 'email',
      'format_champ' => 'email',
      'obligatoire' => 1,
    ),
    'mot_de_passe' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Mot de passe',
      'nom_sql' => 'mot_de_passe',
      'format_champ' => 'mdp_systeme',
    ),
    'entite_acces' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Entite accès',
      'nom_sql' => 'entite_acces',
    ),
    'entite_defaut' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Entite defaut',
      'nom_sql' => 'entite_defaut',
    ),
    'api_cle_publique' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'api_cle_publique',
      'nom_sql' => 'api_cle_publique',
    ),
    'api_cle_privee' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'api_cle_privee',
      'nom_sql' => 'api_cle_privee',
    ),
    'profil_id' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'profil_id',
      'nom_sql' => 'profil_id',
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
                'valeurs' => array (0),
                'nom_sql' => 'extranet',
                'operateur' => 0,
              ),
            ),
          ),
        ), 
      ],
    ),
    'menus_id' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'menus_id',
      'nom_sql' => 'menus_id',
      'type' => 20,
      'liste_choix' => 509,
    ),
    'maquette_id' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'maquette_id',
      'nom_sql' => 'maquette_id',
      'type' => 42,
      'type_element_ajax' => 'maquette',
    ),
    'avatar' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Avatar',
      'nom_sql' => 'avatar',
      'type' => 7,
    ),
    'super_admin' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Super admin',
      'nom_sql' => 'super_admin',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'accueil' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Page d\'accueil',
      'nom_sql' => 'accueil',
      'valeur_defaut' => 'eden/accueil',
    ),
    'raccourcis' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Raccourcis',
      'nom_sql' => 'raccourcis',
      'type' => 6,
    ),
    'acces_toutes_entites' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Accès à toutes les entités',
      'nom_sql' => 'acces_toutes_entites',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'email_mot_de_passe_oublie' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Email mot de passe oublié',
      'nom_sql' => 'email_mot_de_passe_oublie',
    ),
    'equipe' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Equipe',
      'nom_sql' => 'equipe',
      'type' => 42,
      'type_element_ajax' => 'equipe',
    ),
    'langue' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Langue',
      'nom_sql' => 'langue',
    ),
    'droit_usurpation' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Droit usurpation',
      'nom_sql' => 'droit_usurpation',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'remember_token' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'remember_token',
      'nom_sql' => 'remember_token',
    ),
    'autorise_a_modifier_factures_de_plus_de_x_jours' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Autorisé à modifier les anciennes factures',
      'nom_sql' => 'autorise_a_modifier_factures_de_plus_de_x_jours',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'slack_webhook' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Webhook Slack',
      'nom_sql' => 'slack_webhook',
    ),
    'validation_conges_n_plus_1' => 
    array (
      'nom' => 'Validation des congés N+1',
      'type' => 10,
      'type_element_ajax' => 'utilisateur',
    ),
    'validation_conges_n_plus_2' => 
    array (
      'nom' => 'Validation des congés N+2',
      'type' => 10,
      'type_element_ajax' => 'utilisateur',
    ),
    'validation_ndf_n_plus_1' =>
    array (
      'nom' => 'Validation des notes de frais N+1',
      'type' => 10,
      'type_element_ajax' => 'utilisateur',
    ),
    'validation_ndf_n_plus_2' =>
    array (
      'nom' => 'Validation des notes de frais N+2',
      'type' => 10,
      'type_element_ajax' => 'utilisateur',
    ),
    'validation_saisie_temps' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Validateur de saisie des temps',
      'nom_sql' => 'validation_saisie_temps',
      'type' => 10,
      'type_element_ajax' => 'utilisateur',
      'table_pivot' => 'utilisateur_validation_saisie_temps',
    ),
    'validation_jours_travailles' =>
      array (
          'nom' => 'Validateur du suivi des jours travaillés',
          'type' => 10,
          'type_element_ajax' => 'utilisateur',
      ),
    'gestion_ticket' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Gestion ticket',
      'nom_sql' => 'gestion_ticket',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'telephone' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Numéro de téléphone',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone',
    ),
    'cacher_acces_support' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Masquer l’accès au support',
      'nom_sql' => 'cacher_acces_support',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'aide_contextuelle' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Aide contextuelle',
      'nom_sql' => 'aide_contextuelle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'id_microsoft' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'ID Microsoft',
      'nom_sql' => 'id_microsoft',
    ),
    'expiration_token_microsoft' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Expiration Token Microsoft',
      'nom_sql' => 'expiration_token_microsoft',
      'type' => 2,
    ),
    'refresh_token_microsoft' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Refresh Token Microsoft',
      'nom_sql' => 'refresh_token_microsoft',
      'type' => 6,
    ),
    'access_token_microsoft' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Access Token Microsoft',
      'nom_sql' => 'access_token_microsoft',
      'type' => 6,
    ),
    'refresh_token_google' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Refresh Token Google',
      'nom_sql' => 'refresh_token_google',
    ),
    'access_token_google' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Access Token Google',
      'nom_sql' => 'access_token_google',
    ),
    'mode_parametrage' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Mode paramètrage',
      'nom_sql' => 'mode_parametrage',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'type_utilisateur' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Type d\'utilisateur',
      'nom_sql' => 'type_utilisateur',
      'type' => 20,
      'liste_choix' => 160,
    ),
    'autorise_a_se_connecter' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Autorisé à se connecter',
      'format_champ' => 'toggle',
      'nom_sql' => 'autorise_a_se_connecter',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'matricule' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Matricule',
      'nom_sql' => 'matricule',
    ),
    'synchronisation_calendrier_outlook' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Synchronisation calendrier Outlook',
      'format_champ' => 'toggle',
      'nom_sql' => 'synchronisation_calendrier_outlook',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'synchronisation_calendrier_google' =>
      array (
          'nom' => 'Synchronisation calendrier Google',
          'format_champ' => 'toggle',
          'type' => 20,
          'liste_choix' => 14,
      ),
    'date_derniere_synchro_rdv_microsoft' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Date dernière synchronisation',
      'nom_sql' => 'date_derniere_synchro_rdv_microsoft',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'date_derniere_synchro_rdv_google' =>
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Date dernière synchronisation',
      'nom_sql' => 'date_derniere_synchro_rdv_google',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'date_derniere_connexion' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Date dernière connexion',
      'nom_sql' => 'date_derniere_connexion',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'titre' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Titre',
      'nom_sql' => 'titre',
    ),
    'service' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Service',
      'format_champ' => 'toggle',
      'nom_sql' => 'service',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'lien_linkedin' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Lien LinkedIn',
      'format_champ' => 'url',
      'nom_sql' => 'lien_linkedin',
    ),
    'token_initialisation_mot_de_passe' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Token initialisation mot de passe',
      'nom_sql' => 'token_initialisation_mot_de_passe',
    ),
    'autorisation_intranet' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Autorisation Intranet',
      'nom_sql' => 'autorisation_intranet',
      'type' => 20,
      'liste_choix' => 605,
      'valeur_defaut' => '1',
    ),
    'licence_id' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Licence',
      'nom_sql' => 'licence_id',
      'type' => 42,
      'type_element_ajax' => 'licence',
    ),
    'entite_id_defaut' =>
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Entité par défaut',
      'nom_sql' => 'entite_id_defaut',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'type_contrat' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Type de contrat',
      'nom_sql' => 'type_contrat',
      'type' => 20,
      'liste_choix' => 640,
    ),
    'quantite_contrat' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Quantité dans le contrat',
      'nom_sql' => 'quantite_contrat',
      'type' => 2,
    ),
    'code_analytique' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Code analytique',
      'nom_sql' => 'code_analytique',
    ),
    'restrictions_erp' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Restrictions ERP',
      'nom_sql' => 'restrictions_erp',
      'type' => 10,
      'type_reference' => 20,
      'liste_choix' => 20,
      'table_pivot' => 'utilisateur_restrictions_erp',
    ),
    'fonction' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Fonction',
      'nom_sql' => 'fonction',
    ),
    'immatriculation' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Immatriculation',
      'nom_sql' => 'immatriculation',
    ),
    'entites' => 
    array (
      'type_element' => 'utilisateur',
      'nom' => 'Entités',
      'nom_sql' => 'entites',
      'type' => 10,
      'type_element_ajax' => 'entite',
      'table_pivot' => 'utilisateur_entites',
    ),
    'couleur_fond_tache' =>
    array(
      'nom'  => "Couleur de fond de la tâche",
      'type' => 9,
    ),
    'couleur_police_tache' =>
    array(
      'nom'  => "Couleur de police de la tâche",
      'type' => 9,
    ),
    'renouveler_mot_de_passe' => 
    array (
      'nom' => 'Renouveler le mot de passe',
      'type' => 20,
      'liste_choix' => 14,
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
  ),
);