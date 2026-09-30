<?php

return [
		'table_libre' => [
			'nom_table' => "Vue sql",
			'nom_table_sql' => "vue_sql",
			'description' => "",
			'feminin' => "",
			'element' => "vue sql",
			'type_element' => "vue_sql",
			'element_pluriel' => "vues sql",
            'fiche' => 1,

            'disponible_recherche_rapide' => 0,
            'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [

			'nom' => [
				'nom' => 'Nom',
                'aide' => "Correspond au nom de la table libre créée sur l'ERP",
			],
            'nom_sql' => [
				'nom' => 'Nom SQL',
                'aide' => "Correspond au nom SQL de la table libre créée sur l'ERP",
			],
            'joins' => [
				'nom' => 'Joins',
                'aide' => "Correspond à la jointure entre les tables indiquées dans le champ Tables (par exemple : commande_vente.projet_id = projet.id)",
			],
            'tables' => [
				'nom' => 'Tables',
                'aide' => "Correspond aux tables sur laquelles des selects vont être nécessaires (par exemple : commande_vente,projet)",
			],
            'alias_tables' => [
                'nom' => 'Alias tables',
                'aide' => "Correspond aux alias tables sur laquelles des selects vont être nécessaires (par exemple : C,P)",
            ],
            'table_par_defaut' => [
                'nom' => 'Table par défaut',
            ],
            'type_de_vue' => [
                'nom' => 'Type de vue',
                'type' => 20,
                'liste_choix' => 300,
                'valeur_defaut' => "0",
                'aide' => 'Pour les selects : le flux de la création de la vue sera : SELECT champs FROM tables WHERE joins autres_conditions',
            ],
            'requete' => [
                'nom' => 'Requete',
                'type' => 6,
            ],
            'autres_conditions' => [
                'nom' => 'Autres conditions',
                'type' => 6,
                'aide' => "Les autres conditions de la requête sql. Attention des conditions sur les champ inactifs sont déjà implémentés donc vos conditions doivent commencer par AND ou OR ",
            ],
		],
	];
