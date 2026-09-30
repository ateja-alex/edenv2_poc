<?php

return [
	'type_element' => 'profil_droits_element',
	'fiche' => 'profil', 
	'cle_etrangere' => 'profil_id', 

	"colonnes" => [
        array("nom" => "Type élément", "valeur" => "type_element", "ordre" => "1"),
            array("nom" => "Nom sql", "valeur" => "nom_sql", "ordre" => "2"),
            array("nom" => "Entité", "valeur" => "entite_id", "ordre" => "4"),
            array("nom" => "Lecture", "valeur" => "lecture", "ordre" => "5"),
            array("nom" => "Création", 'valeur' => '', 'methode' => 'affichage_champ_creation', "ordre" => "6"),
            array("nom" => "Modification", "valeur" => "modification", "ordre" => "7"),
            array("nom" => "Suppression", "valeur" => "suppression", "ordre" => "8"),
            array("nom" => "Modification en masse", "valeur" => "modification_en_masse", "ordre" => "9"),
            array("nom" => "Suppression en masse", "valeur" => "suppression_en_masse", "ordre" => "10"),
            array("nom" => "Comptabilisation", "valeur" => "comptabilisation", "ordre" => "11"),
            ],
    'calculs' => [
        ],
    'filtres' => [
        ],
];