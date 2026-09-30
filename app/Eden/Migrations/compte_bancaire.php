<?php

return [
		'table_libre' => [
			'nom_table' => "Comptes bancaires",
			'nom_table_sql' => "compte_bancaire",
			'description' => "",
			'feminin' => "",
			'element' => "compte bancaire",
			'type_element' => "compte_bancaire",
			'element_pluriel' => "comptes bancaires",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
			],
			'iban' => [
				'nom' => "IBAN",
			],
			'bic' => [
				'nom' => "BIC",
			],
			'compte_comptable' => [
				'nom' => "Compte comptable",
				'type' => 42,
                'type_element_ajax' => "compte_comptable",
                'format_champ' => "select",
			],
			'journal' => [
				'nom' => "Journal",
				'type' => 42,
      			'type_element_ajax' => 'journal_comptable',
			],
			'utilise_export_sepa' => [
            'nom' => "Utilisé pour l'export SEPA",
            'liste_choix' => 14,
            'type' => 20,
			'format_champ' => "toggle",
			'valeur_defaut' => "0",
        	],
		],
	];