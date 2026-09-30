<?php

namespace App\Eden\Managements\Elements;

class Budget_insight_comptes_management extends Element_management {
	
	/**
	 * 
	 * La modification d'entité est autorisée pour ce type d'élément
	 *
	 */
	public function modification_entite_autorisee() {
		
		return true;
	}
	
	
}