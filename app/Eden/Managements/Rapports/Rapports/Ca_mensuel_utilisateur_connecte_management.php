<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_histogramme_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Ca_mensuel_utilisateur_connecte_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_histogramme_management();
    }
    
	/**
	 * 
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		
		$entites = $this->entites($this->rapport);
		
		$this->rapport->serie(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n1'));
		$this->rapport->serie(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n'));
		
		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$ca_mensuel = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->where('responsable_commercial_id', moi()->id)
						->groupe_par('date_mensuelle')
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
		
		$calcul = new Calcul_gescom_management();
		
		$ca_mensuel_n_moins_1 = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates['n_moins_1'])
						->where('responsable_commercial_id', moi()->id)
						->groupe_par('date_mensuelle')
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
						
		foreach($dates['dates'] as $periode) {
			
			$this->rapport->legende($periode['nom']);
			
			if(isset($ca_mensuel[$periode['periode']]))
				$this->rapport->valeur(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n'), $ca_mensuel[$periode['periode']]);
			else
				$this->rapport->valeur(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n'), 0);
			
			$date_n_moins_1 = date('Y-m', strtotime($periode['periode']." -1 year"));
			
			if(isset($ca_mensuel_n_moins_1[$date_n_moins_1]))
				$this->rapport->valeur(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n1'), $ca_mensuel_n_moins_1[$date_n_moins_1]);
			else
				$this->rapport->valeur(traduction('rapport.ca_mensuel_utilisateur_connecte.ca_ht_n1'), 0);
		}
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}