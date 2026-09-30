<?php

return [
		'table_libre' => [
			'nom_table' => "tableau_de_bord_contenu",
			'nom_table_sql' => "tableau_de_bord_contenu",
			'description' => "",
			'feminin' => "e",
			'element' => "tableau_de_bord_contenu",
			'type_element' => "tableau_de_bord_contenu",
			'element_pluriel' => "tableau_de_bord_contenu",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'tableau_de_bord' => [
				'nom' => "Tableau de bord",
				'type' => 42,
				'type_element_ajax' => 'tableau_de_bord',
				'afficher_sur_formulaire' => 0,
			],
			'type' => [
				'nom' => "Type",
				'type' => 20,
				'liste_choix' => 78,
				'afficher_sur_formulaire' => 1,
				'valeur_defaut' => 1,
                'obligatoire' => 1,
			],
			'type_rapport' => [
				'nom' => "Type de rapport",
			],
			'id_liste' => [
				'nom' => "ID Liste libre",
                'type' => 2,
			],
			'element' => [
				'nom' => "Element",
				'afficher_sur_formulaire' => 0,
			],
			'width' => [
				'nom' => "Largeur",
				'type' => 2,
				'afficher_sur_formulaire' => 0,
   				'valeur_defaut' => '2',
			],
			'height' => [
				'nom' => "Hauteur",
				'type' => 2,
				'afficher_sur_formulaire' => 0,
   				'valeur_defaut' => '2',
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
				'afficher_sur_formulaire' => 0,
			],
			'bloc_parent' => [
				'nom' => "Bloc parent",
				'type' => 2,
				'afficher_sur_formulaire' => 0,
			],
			'theme' => [
				'nom' => "Thème",
				'type' => 20,
				'liste_choix' => 117,
				'afficher_sur_formulaire' => 1,
				'valeur_defaut' => 1,
			],
			'valeurs' => [
				'nom' => "Valeurs",
			],
			'html' => [
				'nom' => "HTML",
				'type' => 6,				
			],
            
		],
	];
