<?php
return [
	'id_rapport' => '',
	'limit' => "0",
	'orderby' => "",
	'orderby_sens' => "",
	'avec_inactifs' => "0",
	'bloquer_tri' => "0",
	'colonnes' => [
		array("nom" => "Ticket", "valeur" => "", "ordre" => "0", "lien_vers_element" => "0", "methode" => "liste_affichage_ticket", "lien_vers_autre_element" => null, "tri_desactive" => "0", "type" => "methode", "champ" => "", "responsive" => "", ),
		array('nom' => 'Crée le', 'valeur' => 'cree_le', 'ordre' => 1),
	],
	'calculs' => [],
	'filtres' => [
        array("nom_sql" => "statut","ordre" => "1"),
        array("nom_sql" => "priorite","ordre" => "2"),
    ],
];