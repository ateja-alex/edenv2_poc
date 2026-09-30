<?php 

return [
		'table_libre' => [
			'nom_table' => "famille_conditions_commerciales",
			'nom_table_sql' => "famille_conditions_commerciales",
			'description' => "",
			'feminin' => "",
			'element' => "famille_conditions_commerciales",
			'type_element' => "famille_conditions_commerciales",
			'element_pluriel' => "famille_conditions_commerciales",
			'fiche' => "0",
			'vue_sql' => "1",
		],
		'champs_libres' => [
            'famille_id' => [
                'nom' => "Famille",
                'type' => "42",
                'type_element_ajax' => "famille",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "famille_id",
            ],
            'famille_origine_id' => [
                'nom' => "Famille origine",
                'type' => "42",
                'type_element_ajax' => "famille",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "famille_id",
            ],
            'client_id' => [
                'nom' => "Client",
                'type' => "42",
                'type_element_ajax' => "client",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "client_id",
            ],
            'catalogue_tarif_id' => [

                'nom' => "Catalogue tarif",
                'type' => "42",
                'type_element_ajax' => "catalogue_tarif",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "catalogue_tarif_id",
            ],
            'palier_quantite' => [
                'nom' => "Palier quantité",
                'type' => "3",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "palier_quantite",
            ],
            'tarif' => [
                'nom' => "Tarif",
                'type' => "3",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "tarif",
            ],
            'remise' => [
                'nom' => "Remise",
                'type' => "3",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "remise",
            ],
            'prix_achat' => [
                'nom' => "Prix achat",
                'type' => "3",
                'type_element_origine' => "condition_commerciale",
                'nom_sql_origine' => "prix_achat",
            ],
		],
		'champs_libres_supprimes' => [
		],
	];