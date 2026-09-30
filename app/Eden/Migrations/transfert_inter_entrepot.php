<?php

return [
        'table_libre' => [
            'nom_table' => "Transfert inter entrepôt",
            'nom_table_sql' => "transfert_inter_entrepot",
            'description' => "",
            'feminin' => "",
            'element' => "transfert inter entrepot",
            'type_element' => "transfert_inter_entrepot",
            'element_pluriel' => "transferts inter entrepôts",
            'fiche' => 1,

            'disponible_recherche_rapide' => 0,
            'creation_rapide' => 0,
            'parametre' => 0,
        ],
        'champs_libres' => [
            'date' => [
                'nom' => "Date",
                'type' => 4,
                'recherche' => 1,
                'obligatoire' => 1,
            ],
            'entrepot_depart_id' => [
                'nom' => "Entrepot de depart",
                'type' => 20,
                'liste_choix' => 8,
                'obligatoire' => 1,
            ],
            'entrepot_arrivee_id' => [
                'nom' => "Entrepot d'arrivee",
                'type' => 20,
                'liste_choix' => 8,
                'obligatoire' => 1,
            ],
            'reserve' => [
                'nom' => "Réservé",
                'type' => 20,
                'liste_choix' => 14,
                'obligatoire' => 1,
            ],
            'pdf' => [
                'nom' => "PDF",
            ],
            'numero' => [
                'nom' => "Numéro",
            ],
        ],
	];