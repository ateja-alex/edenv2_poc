<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Tickets',
    'nom_table_sql' => 'ticket_client',
    'element' => 'ticket',
    'type_element' => 'ticket_client',
    'element_pluriel' => 'tickets',
    'fiche' => 1,
    'module' => 'Gestion commerciale',
    'affichage_recherche' => '#numero# du #date# : #titre# - #client_id# ',
    'affichage_fiche_type' => '#numero# du #date# : #titre# - #client_id# ',
    'affichage_dans_liste' => '#titre#',
    'affichage_pour_select' => '#numero# du #date# : #titre# - #client_id# ',
    'affichage_dans_kanban' => '#numero# du #date# : #titre# - #client_id# ',
  ),
  'champs_libres' => 
  array (
    'titre' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Titre',
      'nom_sql' => 'titre',
      'recherche' => 1,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'description' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Description',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'description',
      'type' => 6,
      'recherche' => 1,
      'afficher_sur_formulaire' => 1,
    ),
    'utilisateur_id' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Affecté à',
      'nom_sql' => 'utilisateur_id',
      'type' => 42,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => 1,
    ),
    'statut' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 150,
      'obligatoire' => 1,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '0',
    ),
    'priorite' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Priorité',
      'nom_sql' => 'priorite',
      'type' => 20,
      'liste_choix' => 67,
      'afficher_sur_formulaire' => 1,
      'valeur_defaut' => '2',
    ),
    'pieces_jointes' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Pièces jointes',
      'nom_sql' => 'pieces_jointes',
      'type' => 15,
    ),
    'client_id' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
    ),
    'contact' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Contact',
      'nom_sql' => 'contact',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'contact',
    ),
    'lien_ticket' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Lien ticket',
      'nom_sql' => 'lien_ticket',
    ),
    'from_email' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'From (email)',
      'nom_sql' => 'from_email',
    ),
    'mail_id' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Mail ID',
      'nom_sql' => 'mail_id',
    ),
    'date' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Date de réception',
      'nom_sql' => 'date',
      'type' => 5,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
    ),
    'entite_id' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 20,
      'liste_choix' => 9,
    ),
    'numero' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Numéro',
      'format_champ' => 'TI{break}{date-Y}{numero}',
      'nom_sql' => 'numero',
      'type' => 14,
      'recherche' => 1,
    ),
    'source' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Source',
      'nom_sql' => 'source',
      'type' => 1,
      'valeur_defaut' => '20',
      'cacher_sans_valeur' => 1,
    ),
    'details_solution' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Détails de la solution',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'details_solution',
      'type' => 6,
    ),
    'type_probleme' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Type de problème',
      'nom_sql' => 'type_probleme',
      'type' => 1,
    ),
    'type_solution' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Type de solution',
      'nom_sql' => 'type_solution',
      'type' => 1,
    ),
    'date_cloture' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Date de clôture',
      'nom_sql' => 'date_cloture',
      'type' => 5,
      'lecture_seule' => 1,
    ),
    'blackliste' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Blacklisté',
      'nom_sql' => 'blackliste',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'nouvel_echange' => 
    array (
      'type_element' => 'ticket_client',
      'nom' => 'Nouvel échange',
      'nom_sql' => 'nouvel_echange',
      'type' => 20,
      'liste_choix' => 14,
    ),
  ),
);