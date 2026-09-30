<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;

class Erreur_management extends Element_management {


	/**
	 * @cf description sur Element_management
	 * 
	 * On traite le cas particulier des coefficients
	 */
	public function enregistre($modifications = array(), $modele = false) {

		if(empty($this->modele) || empty($this->modele->id)) {
			
			$modifications['session'] = json_encode(session()->all()) ;
			$modifications['request'] = json_encode(request()->all()) ; 
		}
		
		return parent::enregistre($modifications, $modele);
	}


	public function alerte_apres_erreur() {
		// TODO : Envoyer un mail
	}

}