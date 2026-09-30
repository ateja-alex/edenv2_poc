<?php

namespace App\Eden\Managements\Elements;

class Treso_creance_client_management extends Element_management {
	
	public function enregistre($modifications = array(), $modele = false) {
		
		// vérification nom
		if(isset($modifications['montants_mensuels'])  && !isset($modifications["client"]) ) {

			return traduction('messages.php.champ_obligatoire').' '.champ_libre('treso_creance_client','client')->modele->nom;
		}

		// vérification montant mensuels
		if(isset($modifications['montants_mensuels']) ){
			
			$montants_mensuels = array();

			// Recupération des mois et des dates
			$dates = array();
			for($i=-1;$i<12;$i++){
	
	            $date = strtotime(''.$i.' months');
	            array_push($dates,date('Y-m', $date).'-01');
			}
	
			for($i=0;$i<13;$i++){
	
				$montants_mensuels[$dates[$i]] = $modifications['montants_mensuels'][$i];
			}
	
			$montants_mensuels_json = json_encode($montants_mensuels);
	
			$modifications['montants_mensuels'] = $montants_mensuels_json;
		
		}
		
		return parent::enregistre($modifications);
	}

	
	
}