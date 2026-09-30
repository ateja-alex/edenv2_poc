<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Encours_client_management extends Rapports_management {

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
	 * On calcule l'encours par client
	 * 
	 */	
    public function genere($ajax = false) {
		
		
		$this->rapport->titres(
            array(
                champ_libre('facture_vente','client_id')->modele->nom,
                champ_libre('facture_vente','solde_document_ttc')->modele->nom,
            )
        );
		
		
		$factures = modele('facture_vente')->where('valide', 1)->zero_ou_null('annulee_par_avoir')->zero_ou_null('regle')->get();
		
		$encours_par_client = array();
		
		$encours_total = 0;
		
		foreach($factures as $facture) {
			
			if(!isset($encours_par_client[$facture->client_id]))
				$encours_par_client[$facture->client_id] = 0;
			
			$encours_par_client[$facture->client_id] += $facture->solde_document_ttc;
			$encours_total += $facture->solde_document_ttc;
		}
		
		arsort($encours_par_client);
		
		foreach($encours_par_client as $client_id => $solde_ttc) {
			
			$this->rapport->ligne(array(management('client', $client_id)->affiche_lien(), $solde_ttc));
		}
		
		$this->rapport->ligne(array(traduction('rapport.encours_client.total'), $encours_total));
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}