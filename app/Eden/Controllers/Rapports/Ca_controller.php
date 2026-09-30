<?php

namespace App\Eden\Controllers\Rapports;

use App\Http\Controllers\Controller;
use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_histogramme_management;

use Illuminate\Http\Request;



/**
 * Ventes par article
 */
class Ca_controller extends Controller {
	
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function mensuel() {
		
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		// on prend uniquement les documents validés
		$calcul->documents_valides_uniquement(false);
		
		$ca_mensuel = $calcul->plus('facture_vente')->groupe_par('date_mensuelle')->resultat();
		
		$legendes = Rapports_management::dates_mensuelles('ca_mensuel');
		
		$rapport = new Rapport_histogramme_management('ca_mensuel', 'CA mensuel', 'Du au');
		
		$rapport->serie('ca_ht');
		
		foreach($legendes as $periode) {
			
			$rapport->legende($periode['nom']);
			
			if(isset($ca_mensuel[$periode['periode']]))
				$rapport->valeur('ca_ht', $ca_mensuel[$periode['periode']]);
			else
				$rapport->valeur('ca_ht', 0);
		}
		
		return view('eden::rapports.rapport', array(
			
			'id_rapport' => 'ca_mensuel',
			'rapport' => $rapport->genere(),
		));
    }
	
	
	
	

}
