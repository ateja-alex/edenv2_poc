<?php

return [
	'type_element' => 'utilisateur',
    'filtres_appliques' => [
	  [
        'operateur' => 0,
	    'blocs' => [],
	    'filtres' => [[
	        'type_element' => 'utilisateur',
            'element_id' => null,
            'champ_liaison' => null,
	        'valeurs' => ["0","1"],
	        'nom_sql' => 'type_utilisateur',
        ]]
      ],
	],
	'desactiver_actions_individuelle' => '[]',
	'lignes_par_page' => '15',
	'creation_taches_en_masse' => '1',
	'afficher_images' => '1',
	'colonnes' => [
		array("nom" => "Avatar", "valeur" => "avatar", "ordre" => "1", "lien_vers_autre_element" => null, "type" => "standard", "responsive" => "1", ),
		array("nom" => "Nom prénom", "valeur" => "#nom# #prenom#", "ordre" => "2", "lien_vers_element" => "1", "tri_par_defaut" => "1", "type" => "concatenation", "responsive" => "1", ),
		array("nom" => "Email", "valeur" => "email", "ordre" => "3", "type" => "standard", ),
		array("nom" => "Profil", "valeur" => "profil_id", "ordre" => "4", "type" => "standard", "responsive" => "1", ),
		array("nom" => "Menus", "valeur" => "menus_id", "ordre" => "5", "type" => "standard", ),
		array("nom" => "Matricule", "ordre" => "6", "type" => "champ", "champ" => "matricule", ),
		array("nom" => "Fonction", "ordre" => "7", "type" => "champ", "champ" => "fonction", ),
		array("nom" => "Service / Code analytique", "ordre" => "8", "type" => "champ", "champ" => "code_analytique", ),
		array("nom" => "Trigramme", "ordre" => "9", "type" => "champ", "champ" => "titre", ),
		array("nom" => "Plaque immat.", "ordre" => "10", "type" => "champ", "champ" => "immatriculation", ),
		array("nom" => "Manager N+1", "ordre" => "11", "type" => "champ", "champ" => "validation_conges_n_plus_1", "responsive" => "1", ),
		array("nom" => "validation N+2", "ordre" => "12", "type" => "champ", "champ" => "validation_conges_n_plus_2", ),
		array("nom" => "Accès intranet", "ordre" => "13", "type" => "champ", "champ" => "autorisation_intranet", "responsive" => "1", ),
		array("nom" => "Équipe", "ordre" => "14", "type" => "champ", "champ" => "equipe", ),
		array("nom" => "Validateur de saisie des temps", "valeur" => "validation_saisie_temps", "ordre" => "15", "type" => "standard", ),
		array("nom" => "Contrat", "ordre" => "16", "type" => "champ", "champ" => "type_contrat", ),
		array("nom" => "Nb Heures/jours", "ordre" => "17", "type" => "champ", "champ" => "quantite_contrat", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "profil_id", "ordre" => "1", ),
		array("nom_sql" => "type_utilisateur", "ordre" => "2", ),
		array("nom_sql" => "autorise_a_se_connecter", "ordre" => "3", ),
		array("nom_sql" => "equipe", "ordre" => "4", ),
	],
];