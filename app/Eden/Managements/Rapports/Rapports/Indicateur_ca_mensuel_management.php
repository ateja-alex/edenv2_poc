<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_indicateur_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Indicateur_ca_mensuel_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_indicateur_management();
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
        
		$calcul = new Calcul_gescom_management();
		
		$calcul->filtre_dates_mensuelles(array('date_debut' => date('Y-m-01'), 'date_fin' => date('Y-m-t')));
		
		$ca = $calcul->plus('facture_vente')->moins('avoir_vente');
		
		$ca = $ca->resultat();
		
		$this->rapport->valeur = $ca;
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}