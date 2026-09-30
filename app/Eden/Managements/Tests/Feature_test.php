<?php

namespace App\Eden\Managements\Tests;

class Feature_test {
	
	/**
	 * 
	 * Récupère le premier ID pour un type élément donné, pour les tests
	 * 
	 */
	public function recupere_premier_id_pour_type_element($type_element) {
		
		$element = modele($type_element)->first();
		
		if($element === null)
			exception("Aucun élément du type $type_element n'a été trouvé dans la BDD : test impossible");
		
		return $element->id;
	}
}