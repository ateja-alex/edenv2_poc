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
class Ca_12_derniers_mois_management extends Rapports_management {
    
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
	 * On affiche les CA sur les 12 derniers mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$entites = $this->entites($this->rapport);

        $this->rapport->titre = traduction('rapport.ca_12_derniers_mois.titre_affichage');
		
		if(empty($entites)) {

			$entites = modele('entite')->get()->pluck('id');
		}
		
		// on va chercher les résultats
		$calcul = new Calcul_gescom_management();
		
		$date_debut = date('Y-m-01', strtotime('now -12 months'));
		$date_fin = date('Y-m-t');
	
		$ca = $calcul->filtre_dates_quotidiennes(array('date_debut' => $date_debut, 'date_fin' => $date_fin))
						->filtre_entites($entites)
						->groupe_par_mois()
						->groupe_par_entite()
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
						
		foreach($entites as $entite_id) {
			
			$entite = modele('entite', $entite_id);
			
			$this->rapport->serie($entite->nom);
			
			$date_courante = $date_debut;
			
			while($date_courante <= $date_fin) {
				
				$index_tableau = formate_date('Y-m', $date_courante);
				
				// on retraite si elles n'existent pas
				if(!isset($ca[$index_tableau]))
					$ca[$index_tableau] = array();
					
				if(!isset($ca[$index_tableau][$entite_id]))
					$ca[$index_tableau][$entite_id] = 0;
									
				// on récupère les valeurs
				if(empty($ca[$index_tableau][$entite_id])) {
					
					$this->rapport->valeur($entite->nom, 'null');
				}
				else {
					
					$this->rapport->valeur($entite->nom, $ca[$index_tableau][$entite_id]);
				}
				
				$date_courante = date('Y-m-01', strtotime($date_courante." next month"));
			}
		}
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}