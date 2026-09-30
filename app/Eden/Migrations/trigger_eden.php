<?php

return [
		'table_libre' => [
			'nom_table' => "Trigger applicatif",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "trigger",
			'type_element' => "trigger_eden",
			'element_pluriel' => "triggers",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Tréso',
		],
		'champs_libres' => [
            'nom' => [
                'nom' => "Nom du trigger",
                'type' => 0,
                'obligatoire' => 1,
            ],
			'type_element_id' => [
				'nom' => "Type élément",
				'type' => 20,
				'liste_choix' => 71,
                'obligatoire' => 1,
			],
            'type_element_concerne_id' => [
				'nom' => "Type élément concerné",
				'type' => 20,
				'liste_choix' => 71,
                'obligatoire' => 1,
			],
            'requete' => [
                'nom' => "Requête",
                'type' => 6,
                'obligatoire' => 1,
                'aide' => "Indiquer la requête de modification à effectuer suite à une modification.
                    Ne pas oublier les conditions pour éviter les inactifs : 'COALESCE(table.inactif,1) != 0'.
                    On peut également utiliser #id_cible# pour ne récupérer que certains éléments.
                    Exemple : 'UPDATE contact SET role=1 WHERE COALESCE(table.inactif,1) != 0 AND client_id = #id_cible#'
                ",
            ],
            'requete_elements_concernes' => [
                'nom' => "Requête éléments concernés",
                'type' => 6,
                'aide' => "Indiquer la requête qui permet de récupérer les éléments qui vont être concernés par la modification pour pouvoir effectuer les actions.
                    Attention il faut que le select soit uniquement 'SELECT table.* FROM ...'.
                    Ne pas oublier les conditions pour éviter les inactifs : 'COALESCE(table.inactif,1) != 0'.
                    On peut également utiliser #id_cible# pour ne récupérer que certains éléments.
                    Exemple : 'SELECT * FROM contact WHERE COALESCE(table.inactif,1) != 0 AND client_id = #id_cible#'
                ",
            ],
            'erreur_requete' => [
                'nom' => "Erreur d'éxécution",
                'type' => 6,
            ],
            'ordre' => [
                'nom' => "Ordre",
                'type' => 2,
            ],
            'type_requete' => [
                'nom' => 'Type de requête',
                'type' => 20,
                'liste_choix' => 715
            ],
            'enregistrement_log' => [
                'nom' => 'Enregistrement des logs',
                'type' => 20,
                'liste_choix' => 14
            ],
            'duree_derniere_execution' => [
			
				'nom' => "Durée de la dernière exécution",
				'type' => 3,
			],
            'date_derniere_execution' => [
                'nom' => "Date de la dernière exécution",
                'type' => 5,
            ],
		],
	];
