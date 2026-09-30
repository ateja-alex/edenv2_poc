<?php

namespace App\Eden\Managements\Elements;

use Jenssegers\Date\Date;

class Treso_revenu_recurrent_management extends Element_management {
	
	public function enregistre($modifications = array(), $modele = false) {
		
		// vérification nom
		if(isset($modifications['montants_mensuels'])  && !isset($modifications["source_revenu"]) ) {

			return traduction('messages.php.champ_obligatoire').' '.champ_libre('treso_revenu_recurrent','source_revenu')->modele->nom;
		}

		// vérification montant mensuels
		if(isset($modifications['montants_mensuels']) ){
			
			$montants_mensuels = array();
	
			// Recupération des mois et des dates
			$dates = array();	
			for($i=0;$i<12;$i++){
	
	            $date = strtotime(''.$i.' months');
	            array_push($dates,date('Y-m', $date).'-01');
			}
	
			for($i=0;$i<12;$i++){
	
				$montants_mensuels[$dates[$i]] = $modifications['montants_mensuels'][$i];
			}
	
			$montants_mensuels_json = json_encode($montants_mensuels);
	
			$modifications['montants_mensuels'] = $montants_mensuels_json;
		
		}

		return parent::enregistre($modifications);
	}

	
	
}