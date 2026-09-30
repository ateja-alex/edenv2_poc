<?php

return [
	'type_element' => 'contact',
	'colonnes' => [
		array("nom" => "Nom", "valeur" => "nom", "ordre" => "2", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Prénom", "valeur" => "prenom", "ordre" => "3", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Poste", "valeur" => "poste", "ordre" => "5", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Adresse email", "valeur" => "adresse_email", "ordre" => "7", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Téléphone", "valeur" => "<a href=\"tel:#telephone#\">#telephone#</a>", "ordre" => "8", "lien_vers_autre_element" => null, "type" => "concatenation", ),
		array("nom" => "Portable", "valeur" => "<a href=\"tel:#telephone_portable#\">#telephone_portable#</a>", "ordre" => "9", "lien_vers_autre_element" => null, "type" => "concatenation", ),
		array("nom" => "Fonction", "valeur" => "fonction", "ordre" => "4", "lien_vers_element" => "1", "lien_vers_autre_element" => null, "type" => "standard", ),
		array("nom" => "Statut", "valeur" => "statut", "ordre" => "8", "type" => "standard", ),
	],
	'calculs' => [
	],
	'filtres' => [
		array("nom_sql" => "fonction", "ordre" => "1", ),
		array("nom_sql" => "statut", "ordre" => "2", ),
	],
];