<?php

return [
	'type_element' => 'compte_comptable',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id"),
		array("nom" => "Compte", "valeur" => "#numero_de_compte# #libelle#", "ordre" => "1", "type" => "concatenation", ),
		array("nom" => "Libellé", "valeur" => "libelle", "ordre" => "3", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
	],
];