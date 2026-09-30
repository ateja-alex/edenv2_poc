<?php

return [

	'type_element' => 'note_de_frais_lignes',
    'fiche' => 'note_de_frais',
    'cle_etrangere' => 'note_de_frais_id',

	'colonnes' => [

		array('nom' => 'Article', 'valeur' => 'article_id', 'ordre' => 0, 'lien_vers_element' => 1),
        array('nom' => 'Montant devise', 'methode' => 'montant_ht_devise', 'ordre' => 2, "type" => "methode"),
		array('nom' => 'HT', 'valeur' => 'montant_ht', 'ordre' => 3),
		array('nom' => 'Taux de tva', 'valeur' => 'taux_tva', 'ordre' => 4),
		array('nom' => 'TTC', 'valeur' => 'montant_ttc', 'ordre' => 5),
        array("nom" => "Montant remboursé", "valeur" => "", "ordre" => 6, "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "1", "type" => "champ", "champ" => "montant_rembourse", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
	],

	'calculs' => [],

	'filtres' => [
	],

    'couleurs' => [
        array(
            "couleur" => "#ff6666",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'note_de_frais_lignes',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => [
                            "montant" => "#note_de_frais_lignes.plafond#",
                            "variable" => "superieur",
                        ],
                        'nom_sql' => 'montant_ttc',
                    ],
                ]
              ],
            ],
        ),
    ],
];