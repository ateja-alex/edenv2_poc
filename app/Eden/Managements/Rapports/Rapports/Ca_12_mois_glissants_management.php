<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_courbe_management;

use Illuminate\Http\Request;

/**
 *
 * Gestion des rapports
 *  
 */
class Ca_12_mois_glissants_management extends Rapports_management {
    
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
	 * On affiche les CA sur 12 mois glissants
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$entites = $this->entites($this->rapport);
		$dates = $this->dates_mensuelles($this->rapport);

        $this->rapport->titre = traduction('rapport.ca_12_mois_glissants.titre_affichage');

		if(empty($entites)) {

			$entites = modele('entite')->get()->pluck('id');
		}
		
		foreach($entites as $entite_id) {
			
			$entite = modele('entite', $entite_id);
			
			$this->rapport->serie($entite->nom);
			
			$date_courante = $dates['date_debut'];
			
			while($date_courante <= $dates['date_fin']) {
				
				// pour chaque mois on calcul le CA sur les 12 derniers mois
				$calcul = new Calcul_gescom_management();
				
				$date_debut = date('Y-m-01', strtotime($date_courante.' -12 months'));
				$date_fin = date('Y-m-t', strtotime($date_courante.' last month'));
				
				$ca = $calcul->filtre_dates_quotidiennes(array('date_debut' => $date_debut, 'date_fin' => $date_fin))
								->filtre_entites([$entite_id])
								->plus('facture_vente')
								->moins('avoir_vente')
								->resultat();
				
				// on récupère les valeurs
				if(empty($ca)) {
					
					$this->rapport->valeur($entite->nom, 'null');
				}
				else {
					
					$this->rapport->valeur($entite->nom, $ca);
				}
				
				$date_courante = date('Y-m-01', strtotime($date_courante." next month"));
			}
		}
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}