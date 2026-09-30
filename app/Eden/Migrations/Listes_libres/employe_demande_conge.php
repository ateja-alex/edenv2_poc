<?php

return [
	'type_element' => 'employe_demande_conge',
	'colonnes' => [
		array("nom" => "#", "valeur" => "id"),
		array("nom" => "Employé", "valeur" => "employe_id", "ordre" => "1"),
		array("nom" => "Date de début", "valeur" => "#date_de_debut# #periode_de_debut#", "ordre" => "2", "type" => "concatenation"),
		array("nom" => "Date de fin", "valeur" => "#date_de_fin# #periode_de_fin#", "ordre" => "3", "type" => "concatenation"),
		array("nom" => "Validé par le N+1", "valeur" => "valide_n1", "ordre" => "4"),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "5"),
		array("nom" => "Raison", "valeur" => "raison", "ordre" => "6"),
		array("nom" => "Nombre de jours", "valeur" => "nombre_jours", "ordre" => "7"),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "statut", ),
		array("nom_sql" => "raison", "ordre" => "1", ),
		array("nom_sql" => "date_de_debut", "ordre" => "2", ),
		array("nom_sql" => "employe_id", "ordre" => "3", ),
	],
];