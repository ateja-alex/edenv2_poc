<?php

return [
    'table_libre' => [
        'nom_table' => "Commande achat lignes",
        'nom_table_sql' => "",
        'description' => "",
        'feminin' => "",
        'type_element' => "commande_achat_lignes",
        'element' => 'commande achat ligne',
        'element_pluriel' => 'commande achat lignes',
        'fiche' => 0,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'module' => 'Gestion commerciale',
    ],
    'champs_libres' => [
        'article_id' => [
            'nom' => "Article",
            'type' => 42,
            'type_element_ajax' => 'article',
            'recherche' => 1,
            'index' => true,
        ],
        'document_id' => [
            'nom' => "Commande",
            'type' => 42,
            'type_element_ajax' => 'commande_achat',
            'recherche' => 1,
            'index' => true,
        ],
        'fournisseur_id_ligne' => [
            'nom' => "Fournisseur",
            'type' => 42,
            'type_element_ajax' => 'fournisseur',
            'recherche' => 1,
        ],
        'ligne' => [
            'nom' => "Ligne",
            'type' => 2,
        ],
        'designation' => [
            'nom' => "Désignation",
            'recherche' => 1,
        ],
        'tarif' => [
            'nom' => "Tarif",
            'type' => 3,
        ],
        'quantite' => [
            'nom' => "Quantité",
            'type' => 3,
        ],
        'remise' => [
            'nom' => "Remise",
            'type' => 3,
        ],
        'tva' => [
            'nom' => "Tva",
            'type' => 3,
        ],
        'type_tarif' => [
            'nom' => "Type tarif",
            'type' => 2,
        ],
        'remise_globale_ligne' => [
            'nom' => 'Remise globale',
            'type' => 3,
        ],
        'tarif_saisi' => [
            'nom' => 'Tarif saisi',
            'type' => 3,
        ],
        'style_ligne_document_id' => [
            'nom' => "Style ligne",
            'type' => 42,
            'type_element_ajax' => 'style_ligne_document',
        ],
        'afficher_photo' => [
            'nom' => "Afficher photo",
            'type' => 20,
            'liste_choix' => 14,
        ],
        'code_article' => [
            'nom' => 'Code Article',
        ],
        'prix_achat' => [
            'nom' => 'Prix achat',
            'type' => 3,
        ],
        'description' => [
            'nom' => 'Description',
            'type' => 6,
        ],
        'numero_de_serie' => [
            'nom' => 'Numéro de série',
        ],
        'numeros_de_lot' => [
            'nom' => 'Numéro de lot',
        ],
        'unite' => [
            'nom' => 'Unité',
            'type' => 42,
            'type_element_ajax' => 'article_unite',
        ],
        'disponibilite' => [
            'nom' => 'Disponibilité',
        ],
        'declinaison_id' => [
            'nom' => 'Déclinaison',
            'type' => 42,
            'type_element_ajax' => 'article_declinaison',
        ],
        'tarif_apres_remise' => [
            'nom' => 'Tarif après remise',
            'type' => 3,
        ],
        'conditionnement' => [
            'nom' => 'Conditionnement',
            'type' => 42,
            'type_element_ajax' => 'conditionnement'
        ],
        'calculateur' => [
            'nom' => 'Calculateur',
            'type' => 6,
        ],
        'tarif_net' => [
            'nom' => 'Tarif net',
            'type' => 3,
        ],
        'total' => [
            'nom' => 'Tarif total',
            'type' => 3,
        ],
        'marge_pourcentage' => [
            'nom' => 'Marge (%)',
            'type' => 3,
        ],
		'type_element_source' => [
            'nom' => 'Type document origine',
            'type' => 0,
        ],
        'id_element_source' => [
            'nom' => 'ID document origine',
            'type' => 2,
        ],
        'id_ligne_source' => [
            'nom' => 'ID ligne origine',
            'type' => 2,
        ],
		'transforme' => [
            'nom' => 'Traité',
            'type' => 20,
            'liste_choix' => 302,
        ],
        'transforme_reliquat' => [
            'nom' => 'Reliquat',
            'type' => 3,
        ],
		'date_de_reception' => [
			'nom' => "Date de réception",
			'type' => 4,
		],
		'recue' => [
			'nom' => "Reçue",
			'type' => 20,
			'liste_choix' => 14,
		],
		'quantite_recue' => [
			'nom' => "Quantitée reçue",
			'type' => 3,
		],
		'reliquat_reception' => [
			'nom' => "Reliquat réception",
			'type' => 3,
		],
		'quantite_colisee' => [
			'nom' => "Quantité colisée",
			'type' => 3,
		],
		'conditionnement_id' => [
			'nom' => "Conditionnement",
			'type' => 42,
			'type_element_ajax' => "article_fournisseur",
		],
        'regroupement_id' => [
            'nom' => 'ID regroupement',
            'type' => 2,
        ],
        'couleur_regroupement' => [
            'nom' => 'Couleur regroupement',
            'type' => 9,
        ],
		'nomenclature_ligne_parent' => [
            'nom' => 'Ligne parent nomenclature',
            'type' => 42,
            'type_element_ajax' => 'commande_achat_lignes',
        ],
		'receptionne_par' => [
            'nom' => 'Réceptionné par',
            'type' => 10,
            'type_element_ajax' => 'utilisateur',
        ],
        'valide' => [
            'nom' => "Valide",
            'type' => 20,
            'liste_choix' => 14,
        ],
        'utilisateur_id' => [
            'nom' => 'Utilisateur',
            'type' => 42,
            'type_element_ajax' => 'utilisateur',
        ],
        'categorie_comptable_article_id' => [
            'nom' => 'Catégorie comptable article',
            'type' => 42,
            'type_element_ajax' => 'categorie_comptable_article',
            'filtres' => [
                'categorie_comptable_article' => array (
                    array (
                        'operateur' => 0,
                        'exclu' => 0,
                        'blocs' => array (),
                        'filtres' => 
                        array (
                            array (
                                'type_element' => 'categorie_comptable_article',
                                'champ_liaison' => NULL,
                                'valeurs' => 'lien_champ|commande_achat_lignes.article_id',
                                'nom_sql' => 'article_id',
                                'operateur' => 0,
                            ),
                        ),
                    ),
                ), 
            ],
        ],
        'suppression_manuelle_reliquat' => [
            'nom' => 'Suppression manuelle du reliquat',
            'type' => 20,
            'liste_choix' => 14,
        ],
    ],
];
