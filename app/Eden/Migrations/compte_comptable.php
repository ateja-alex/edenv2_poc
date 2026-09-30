<?php

return [
		'table_libre' => [
			'nom_table' => "Comptes comptables",
			'nom_table_sql' => "compte_comptable",
			'description' => "",
			'feminin' => "",
			'element' => "Compte comptable",
			'type_element' => "compte_comptable",
			'element_pluriel' => "Comptes comptables",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 0,
            'affichage_dans_liste' => '#numero_de_compte#, #libelle#',
		],
		'champs_libres' => [
			'numero_de_compte' => [
				'nom' => "Numéro",
				'obligatoire' => 1,
                'afficher_sur_formulaire' => 1,
            ],
			'libelle' => [
				'nom' => "Libellé",
				'obligatoire' => 1,
                'afficher_sur_formulaire' => 1,
            ],
		],
	];