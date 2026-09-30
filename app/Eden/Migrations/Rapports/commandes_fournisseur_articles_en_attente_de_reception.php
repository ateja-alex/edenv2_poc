<?php

return [

	'categorie' => 'activite_operationnelle',
	'icone' => 'table',
	'titre' => 'Articles en attente de réception',
	'description' => "Liste des articles fournisseurs en attente de réception",
	'ordre' => 11,
	'inactif' => 0,
	
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'commande_achat_ligne_fournisseur',
		'desactiver_export' => 1,
		'desactiver_actions' => 1,
		
		'colonnes' => [
			array("nom" => "#", "valeur" => "id", "ordre" => "0", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", ),
			array("nom" => "Article", "valeur" => "article_id", "ordre" => "1", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "article_id", "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "0", "standard" => "", ),
			array("nom" => "Commande achat", "valeur" => "commande_achat_id", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "commande_achat_id", "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "0", "standard" => "", ),
			array("nom" => "Reçue", "valeur" => "recu", "ordre" => "3", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "champ", "champ" => "recu", "responsive" => "", "caracteres_max" => "0", "standard" => "", ),
			array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "4", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", ),
			array("nom" => "Date de reception", "valeur" => "date_de_reception", "ordre" => "5", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "champ", "champ" => "date_de_reception", "responsive" => "", "caracteres_max" => "0", "standard" => "", ),
			array("nom" => "Fournisseur", "valeur" => "fournisseur_id", "ordre" => "6", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => "fournisseur_id", "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "0", "standard" => "", ),
		],
		'calculs' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date_de_reception']],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'commande_achat_ligne_fournisseur',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'recu',
                ],
            ]
          ],
        ],
		
	],
];