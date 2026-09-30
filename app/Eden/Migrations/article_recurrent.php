<?php 

return [
		'table_libre' => [
			'nom_table' => "Article récurrent",
			'nom_table_sql' => "article_recurrent",
			'element' => "article récurrent",
			'type_element' => "article_recurrent",
			'element_pluriel' => "articles récurrents",
			'vue_sql' => "1",
            'creation_rapide' => 0,
            'fiche' => 0,

            'disponible_recherche_rapide' => 0,
		],
		'champs_libres' => [
            'client_id' => [
                'nom' => "Client",
                'type' => "42",
                'type_element_ajax' => "client",
                'type_element_origine' => "facture_vente",
                'nom_sql_origine' => "client_id",
            ],
            'recurrence_id' => [
                'nom' => "Récurrence",
                'type' => "42",
                'type_element_ajax' => "eden_recurrence_elements",
                'type_element_origine' => "facture_vente",
                'nom_sql_origine' => "id_recurrence",
            ],
            'type_document' => [
                'nom' => "Type document",
                'type_element_origine' => "eden_recurrence_elements",
                'nom_sql_origine' => "type_element",
            ],
            'document_id' => [
                'nom' => "Document id",
                'type' => 2,
                'type_element_origine' => "facture_vente_lignes",
                'nom_sql_origine' => "document_id",
            ],
            'article_id' => [
                'nom' => "Article",
                'type' => 42,
                'type_element_ajax' => 'article',
                'type_element_origine' => "facture_vente_lignes",
                'nom_sql_origine' => "article_id",
            ],
            'famille_id' => [
                'nom' => "Famille",
                'type' => 42,
                'type_element_ajax' => 'famille',
                'type_element_origine' => "article",
                'nom_sql_origine' => "famille_id",
            ],
            'quantite' => [
                'nom' => "Quantité",
                'type' => 3,
                'type_element_origine' => "facture_vente_lignes",
                'nom_sql_origine' => "quantite",
            ],
            'tarif' => [
                'nom' => "Tarif",
                'type' => 3,
                'type_element_origine' => "facture_vente_lignes",
                'nom_sql_origine' => "tarif",
            ],
            'depuis_le' => [
                'nom' => "Depuis le",
                'type' => 5,
                'type_element_origine' => "eden_recurrence_elements",
                'nom_sql_origine' => "rdi_prochaine_occurence",
            ],
            'jusquau' => [
                'nom' => "Jusqu'au",
                'type' => 5,
                'type_element_origine' => "eden_recurrence_elements",
                'nom_sql_origine' => "rdi_prochaine_occurence",
            ],
            'mode_recurrence' => [
                'nom' => "Mode récurrence",
                'type' => 20,
                'liste_choix' => 60,
                'type_element_origine' => "eden_recurrence_elements",
                'nom_sql_origine' => "mode_recurrence",
            ],
            'actif' => [
                'nom' => "Actif",
                'type' => 20,
                'liste_choix' => 14,
                'type_element_origine' => "facture_vente",
                'nom_sql_origine' => "valide",
            ]
		],
	];