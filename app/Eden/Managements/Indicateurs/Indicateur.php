<?php

namespace App\Eden\Managements\Indicateurs;

class Indicateur {

	/**
	 *
	 * Effectue un recalcul standard en passant par la méthode calcule()
	 *
	 */
	public function calcul_individuel_standard($type_element) {
		
		$elements = modele($type_element)->get();
		
		foreach($elements as $element) {
			
			$this->calcule(management($type_element, $element->id));
		}
		
		return true;
	}

	
}
