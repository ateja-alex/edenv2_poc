<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_histogramme_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Pipe_commercial_management extends Rapports_management {

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
		
		$this->rapport->serie('montant');
		
		$statuts = management('projet')->champ('statut')->valeurs_possibles;
		
		$montant_par_statut = modele('projet')
								->select(\DB::raw('statut, SUM(valeur_ponderee) as montant_total'))
								->groupBy('statut')
								->get()
								->pluck('montant_total', 'statut')
								->toArray();
		
		foreach($statuts as $id => $nom) {
			
			// on n'affiche pas les clôturées
			if($id == 350)
				continue;
			
			$this->rapport->legende($nom);
			
			if(isset($montant_par_statut[$id]))
				$this->rapport->valeur('montant', $montant_par_statut[$id]);
			else
				$this->rapport->valeur('montant', 0);
		}
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}