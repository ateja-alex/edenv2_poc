<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_courbe_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_7_prochains_jours_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_courbe_management();
    }
    
	/**
	 * 
	 * On calcule le CA pour les 7 prochains jours
	 * 
	 */	
    public function genere($ajax = false) {
		
		$calcul = new Calcul_gescom_management();
		
		$ca_7_prochains_jours = $calcul->documents_valides_uniquement(false)
						->filtre_dates_quotidiennes(array('date_debut' => date('Y-m-d'), 'date_fin' => date('Y-m-d', strtotime('now +6 days'))))
						->plus('facture_vente')
						->groupe_par('entite')
						->groupe_par('date')
						->resultat();
		
		$entites = modele('entite')->get();
		
		foreach($entites as $entite) {
			
			$this->rapport->serie($entite->nom);
		}
		
		$date_courante = date('Y-m-d');
		
		while($date_courante <= date('Y-m-d', strtotime("now +6 days"))) {
			
			$this->rapport->legende(formate_date('d/m', $date_courante));
			
			foreach($entites as $entite) {
				
				if(isset($ca_7_prochains_jours[$entite->id]) && isset($ca_7_prochains_jours[$entite->id][$date_courante])) {
					
					$this->rapport->valeur($entite->nom, $ca_7_prochains_jours[$entite->id][$date_courante]);
				}
				else {
					
					$this->rapport->valeur($entite->nom, 0);
				}
			}
			
			$date_courante = date('Y-m-d', strtotime($date_courante." +1 day"));
		}

		return $this->rapport->genere($ajax);
    }
	
	
}