<?php

/*
*
* Helper pour aller chercher le modele pour un élément
*
* @param $type_element string
*
*/
function modele($type_element, $id_element = false) {

	$tests = array(

		"\\App\\Models\\Elements\\".ucfirst($type_element),
		"\\App\\Eden\\Models\\Elements\\".ucfirst($type_element),
		"\\App\\Eden\\Models\\Elements\\Element",
	);

	$classe = classe_existante($tests);

	$modele = new $classe();

	// c'est le cas standard on va chercher les infos en bdd
	$modele->informations_modele($type_element);
	
	// on doit aller chercher un élément
	if($id_element !== false) {

		// on enregistre au cas où l'id_element fourni n'existe pas
		$modele_avant_recherche = clone($modele);
		
        if($type_element == 'tache')
            $modele = $modele->avec_parents();

		$modele = $modele->avec_inactifs()->find($id_element);
		
		if($modele !== null) {

			$modele->informations_modele($type_element);
			return $modele;
		}

		$modele = $modele_avant_recherche;
	}

	return $modele;
}
