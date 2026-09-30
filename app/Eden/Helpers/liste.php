<?php

use App\Eden\Models\Rapport_libre;
use App\Eden\Managements\Listes_management;

/*
*
* Helper pour aller chercher un management pour une liste
*
* @param $id_rapport string
*
*/
function liste($type_element, $id_rapport = false) {
	
	if(!empty($id_rapport))
		return liste_rapport($id_rapport);
	
	
	return liste_type_element($type_element);
}

function liste_rapport($id_rapport) {

	$tests = array(
		
		// "\\App\\Managements\\Rapports\\".ucfirst($id_rapport)."_management",
		"\\App\\Managements\\Listes\\Rapport_".$id_rapport."_management",
		"\\App\\Eden\\Managements\\Listes\\Rapport_".$id_rapport."_management",
        "\\App\\Managements\\Listes\\Liste_".$id_rapport."_management",
        "\\App\\Eden\\Managements\\Listes\\Liste_".$id_rapport."_management",
		"\\App\\Eden\\Managements\\Listes_management",
	);

	$classe = classe_existante($tests);

	if(!empty($classe)) {

		$manager = new $classe($id_rapport);

		return $manager;
	}

}

function liste_type_element($type_element) {
	

	
	$tests = array(
		
		// "\\App\\Managements\\Rapports\\".ucfirst($id_rapport)."_management",
		"\\App\\Eden\\Managements\\Listes\\Liste_".$type_element."_management",
		"\\App\\Eden\\Managements\\Listes_management",
	);

	$classe = classe_existante($tests);

	if(!empty($classe)) {

		$manager = new $classe();

		return $manager;
	}

	
	return false;
	
}