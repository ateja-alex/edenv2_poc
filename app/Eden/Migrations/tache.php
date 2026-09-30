<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Taches',
    'nom_table_sql' => 'tache',
    'feminin' => 'e',
    'element' => 'tache',
    'type_element' => 'tache',
    'element_pluriel' => 'taches',
    'affichage_recherche' => '#titre# #client_id# ',
    'affichage_fiche_type' => '#titre# #client_id# ',
    'affichage_dans_liste' => '#titre# #client_id# ',
    'affichage_pour_select' => '#titre# #client_id# ',
    'editable_client' => 1,
  ),
  'champs_libres' => 
  array (
    'client_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'projet_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'type_element_ajax' => 'projet',
    ),
    'ticket_client_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Ticket',
      'nom_sql' => 'ticket_client_id',
      'type' => 42,
      'type_element_ajax' => 'ticket_client',
    ),
    'titre' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Titre',
      'nom_sql' => 'titre',
      'obligatoire' => 1,
    ),
    'date_de_debut' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Date',
      'nom_sql' => 'date_de_debut',
      'type' => 5,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
      'modifier_en_masse' => 1,
    ),
    'date_de_fin' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Date de fin',
      'nom_sql' => 'date_de_fin',
      'type' => 5,
      'valeur_defaut' => '#aujourdhui+1h#',
      'modifier_en_masse' => 1,
    ),
    'affectation' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Affectation',
      'nom_sql' => 'affectation',
      'type' => 42,
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => "1",
      'modifier_en_masse' => 1,
    ),
    'terminee' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Terminée',
      'nom_sql' => 'terminee',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'commentaire' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Commentaire',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'commentaire',
      'type' => 6,
    ),
    'notification_email_active' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Notification par email activée',
      'nom_sql' => 'notification_email_active',
      'type' => 20,
      'liste_choix' => 14,
      'format_champ' => 'toggle',
    ),
    'notification_email_combien' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Délai avant notification par email',
      'nom_sql' => 'notification_email_combien',
      'type' => 2,
      'valeur_defaut' => '15',
    ),
    'notification_email_unite' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Unité notification par email',
      'nom_sql' => 'notification_email_unite',
      'valeur_defaut' => 'M',
    ),
    'notification_visuelle_active' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Notification par visuelle activée',
      'nom_sql' => 'notification_visuelle_active',
      'type' => 20,
      'liste_choix' => 14,
      'format_champ' => 'toggle',
    ),
    'notification_visuelle_combien' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Délai avant notification par visuelle',
      'nom_sql' => 'notification_visuelle_combien',
      'type' => 2,
      'valeur_defaut' => '15',
    ),
    'notification_visuelle_unite' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Unité notification par visuelle',
      'nom_sql' => 'notification_visuelle_unite',
      'valeur_defaut' => 'M',
    ),
    'entite_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'id_feuille_de_temps' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Feuille de temps',
      'nom_sql' => 'id_feuille_de_temps',
      'type' => 2,
    ),
    'type_element' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Type element',
      'nom_sql' => 'type_element',
    ),
    'element_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Element ID',
      'nom_sql' => 'element_id',
      'type' => 2,
    ),
    'id_microsoft' => 
    array (
      'type_element' => 'tache',
      'nom' => 'id microsoft',
      'nom_sql' => 'id_microsoft',
    ),
    'id_google' => 
    array (
      'type_element' => 'tache',
      'nom' => 'id google',
      'nom_sql' => 'id_google',
    ),
    'couleur_fond' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Couleur fond',
      'nom_sql' => 'couleur_fond',
      'type' => 9,
    ),
    'couleur_texte' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Couleur texte',
      'nom_sql' => 'couleur_texte',
      'type' => 9,
    ),
    'urgent' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Urgent',
      'format_champ' => 'toggle',
      'nom_sql' => 'urgent',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'type_tache_todo' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Type de tâche',
      'nom_sql' => 'type_tache_todo',
      'type' => 20,
      'liste_choix' => 503,
      'cacher_sans_valeur' => 1,
    ),
    'type_tache_rdv' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Type de rendez-vous',
      'nom_sql' => 'type_tache_rdv',
      'type' => 20,
      'liste_choix' => 504,
      'cacher_sans_valeur' => 1,
    ),
    'groupe_affectations' => array(
        'type_element' => 'tache',
        'nom' => 'Groupe d\'affectations',
        'nom_sql' => 'groupe_affectations',
        'type' => 2,
        'index' => 1,
    ),
    'prive' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Rendez-vous privé',
      'format_champ' => 'toggle',
      'nom_sql' => 'prive',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'tache_parent' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Tâche parent',
      'nom_sql' => 'tache_parent',
    ),
    'recurrence_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'ID récurrence',
      'nom_sql' => 'recurrence_id',
      'type' => 2,
    ),
    'parent_id' => 
    array (
      'type_element' => 'tache',
      'nom' => 'ID tâche parent',
      'nom_sql' => 'parent_id',
      'type' => 2,
    ),
    'journee_entiere' => 
    array (
      'type_element' => 'tache',
      'nom' => 'Journée entière',
      'format_champ' => 'toggle',
      'nom_sql' => 'journee_entiere',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'exception_recurrence' => 
    array (
      'nom' => 'Exception récurrence',
      'format_champ' => 'toggle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'participant' =>
    array (
      'nom' => 'Journée entière',
      'format_champ' => 'toggle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'id_tache_organisateur' =>
    array (
      'nom' => 'ID de la tâche de l\'organisateur',
      'type' => 42,
      'type_element_ajax' => 'tache',
    ),
    'id_commun_taches_participants' =>
    array (
      'nom' => 'ID commun des tâches de participants',
    ),
    'adresse_email_organisateur' =>
    array (
      'nom' => "Adresse email de l'organisateur",
      'format_champ' => "email",
    ),
    'statut_participant' =>
    array (
      'nom' => 'Statut du participant',
      'type' => 20,
      'liste_choix' => 23,
      'valeur_defaut' => 0,
    ),
    'annulee' =>
    array (
      'nom' => 'Annulée',
      'format_champ' => 'toggle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'visioconference' =>
    array (
      'nom' => 'Visioconférence',
      'format_champ' => 'toggle',
      'type' => 20,
      'liste_choix' => 14,
    ),
  ),
);