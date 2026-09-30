<?php

/*
*
* Helper pour aller chercher un management pour un élément
*
* @param $type_element string
*
*/
function management($type_element, $id_element = false, $modele = false) {

	$tests = array(
		
		"\\App\\Managements\\Elements\\".ucfirst($type_element)."_management",
		"\\App\\Eden\\Managements\\Elements\\".ucfirst($type_element)."_management",
		"\\App\\Eden\\Managements\\Elements\\Element_management",
	);

	$classe = classe_existante($tests);

	if(empty($classe))
		return null;

	return new $classe($type_element, $id_element, $modele);
}

/*
*
* Helper pour aller chercher un management pour un module de fonctionnalité
*
* @param $type_element string
*
*/
function management_fonctionnalite($type_element) {

    $management_general = new App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

    return $management_general->recupere_management_fonctionnalite($type_element);
}