<?php

return [
	'id_rapport' => '',
	'limit' => "",
	'orderby' => "",
	'orderby_sens' => "",
	'avec_inactifs' => "",
	'bloquer_tri' => "",
	
	'colonnes' => [
		array("nom" => "#", "valeur" => "id", "ordre" => "0", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
		array("nom" => "Sujet", "valeur" => "<b>#date#</b> : #sujet#", "ordre" => "1", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "concatenation", "champ" => "", "responsive" => "", ),
		array("nom" => "Destinataires", "valeur" => "<b>De :</b> #from#<br/> <b>A :</b> #to#<br/><b>Copie : </b> #cc#", "ordre" => "2", "lien_vers_element" => "0", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "concatenation", "champ" => "", "responsive" => "", ),
		array("nom" => "Pièces jointes", "valeur" => "", "ordre" => "4", "lien_vers_element" => "", "methode" => "colonne_affichage_pieces_jointes", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
		array("nom" => "Client ID", "valeur" => "client_id", "ordre" => "5", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => "client_id", "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
		array("nom" => "Public", "valeur" => "public", "ordre" => "6", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
		array("nom" => "Traité", "valeur" => "traite", "ordre" => "7", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
		array("nom" => "En charge", "valeur" => "en_charge", "ordre" => "12", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", ),
	],
	
	'calculs' => [],
	
	'filtres' => [
		array("nom_sql" => "public", "type_element" => "", ),
		array("nom_sql" => "synchro_mail_id", "type_element" => "", ),
		array("nom_sql" => "traite", "type_element" => "", ),
		array("nom_sql" => "en_charge", "type_element" => "", ),
		],
];