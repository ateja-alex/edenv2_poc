<?php

namespace App\Eden\Managements\Elements;

class Modalite_paiement_management extends Element_management {

	/**
	 * 
	 * On calcule la date de règlement en fonction de la date de facturation & du délai de paiement
	 * 
	 */
	public function calcule_date_reglement($date_facturation) {
		
        $modalite_paiement = $this->modele;

		if(empty($modalite_paiement))
			return array('en' => formate_date('Y-m-d', $date_facturation), 'fr' => formate_date('d/m/Y', $date_facturation));
		
		if(!empty($modalite_paiement->nombre_de_jours))
            $date_facturation = date('Y-m-d', strtotime($date_facturation . " +" . $modalite_paiement->nombre_de_jours . " days "));
        
		if(!empty($modalite_paiement->fin_de_mois))
			$date_facturation = date('Y-m-t', strtotime($date_facturation));
			
		if(!empty($modalite_paiement->le)) {
			
			$jour = formate_date('d', $date_facturation);

			if($jour > $modalite_paiement->le) {
				
				$date_facturation = date('Y-m-'.substr('0'.$modalite_paiement->le, -2), strtotime(date('Y-m-01', strtotime($date_facturation)).' +1 month'));			
			}
			else {
				
				$date_facturation = date('Y-m-'.substr('0'.$modalite_paiement->le, -2), strtotime($date_facturation));
			}
		}
		
		return $date_facturation;
	}
}
