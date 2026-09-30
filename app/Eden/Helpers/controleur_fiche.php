<?php

/*
*
* Helper pour aller chercher le bon controleur pour les fiches
*
* @param $type_element string
* @param $id_element int
*
*/
function controleur_fiche($type_element, $id_element) {
	
	$controleurs = array(

		"\\App\\Http\\Controllers\\Fiches\\" . ucfirst($type_element).'_controller',
		"\\App\\Eden\\Controllers\\Fiches\\" . ucfirst($type_element).'_controller',
		"\\App\\Eden\\Controllers\\Fiche_controller",
	);


	foreach($controleurs as $controleur) {
		
		if(class_exists($controleur)) {
			
			$management = new $controleur($type_element);
			
			$management->type_element = $type_element;
			$management->id_element = $id_element;
			
			return $management;
		}
	}
}
