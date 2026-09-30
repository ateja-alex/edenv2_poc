<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Tableau_de_tva_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On calcule le tableau de TVA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		$this->export_excel($this->rapport);
		
		
		// on fait le calcul
		// on va chercher le CA mensuel
		$calcul = new Calcul_gescom_management();
		
		$base_ht_par_tva = $calcul->documents_valides_uniquement(false)
						->groupe_par('mois')
						->groupe_par('tva')
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();

        $titres = array('periode','ca_ht', 'taux_tva', 'tva');

        foreach($titres as &$titre){
            if($titre != 'periode')
                $titre = array(traduction('rapport.tableau_de_tva.colonnes.'.$titre),'css_montant');
            else
                $titre = traduction('rapport.tableau_de_tva.colonnes.'.$titre);
        }

		$this->rapport->titres($titres);

		foreach($dates['dates'] as $info_par_date) {
			
			if(!isset($base_ht_par_tva[$info_par_date['periode']]))
				continue;
			
			$this->rapport->sous_titre($info_par_date['nom']);
			
			foreach($base_ht_par_tva[$info_par_date['periode']] as $tva => $montant) {

                $tva = is_numeric($tva) ? $tva : 0;

				$info = array(
				
					$info_par_date['nom'],
					array(montant($montant,0), ' css_montant'),
					array($tva.'%', ' css_montant'),
					array(montant($montant * $tva / 100,0), ' css_montant'),
				);
				
				$this->rapport->ligne($info);
			}
			
			
		}
		
		return $this->rapport->genere($ajax);
    }
	
	
}