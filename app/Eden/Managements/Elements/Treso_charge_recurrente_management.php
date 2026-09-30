<?php

namespace App\Eden\Managements\Elements;

use Jenssegers\Date\Date;

class Treso_charge_recurrente_management extends Element_management {
	
	public function enregistre($modifications = array(), $modele = false) {
		
		// vérification nom

		if(isset($modifications['mois_selectionnes'])  && !isset($modifications["charge"]) ) {

			return traduction('messages.php.champ_obligatoire').' '.champ_libre('treso_charge_recurrente','charge')->modele->nom;
		}

		


		// vérification montant mensuels
		
		if(isset($modifications['mois_selectionnes']) ){
			
			$mois_selectionnes_json = json_encode($modifications['mois_selectionnes']);
	
			$modifications['mois_selectionnes'] = $mois_selectionnes_json;
		
		}
		
		return parent::enregistre($modifications);
	}
	

	
	
}