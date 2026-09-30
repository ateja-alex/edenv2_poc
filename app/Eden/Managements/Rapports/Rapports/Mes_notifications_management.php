<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_html_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Mes_notifications_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_html_management();
    }
    
	/**
	 * 
	 * On calcule le tableau de TVA par mois
	 * 
	 */	
    public function genere($ajax = false) {
        
		$this->rapport->parametres_pour_vue['notifications'] = modele('notification')->where('utilisateur_id', moi()->id)->orderBy('date', 'desc')->limit(10)->get();
		
		return $this->rapport->genere($ajax);
    }
	
	
}