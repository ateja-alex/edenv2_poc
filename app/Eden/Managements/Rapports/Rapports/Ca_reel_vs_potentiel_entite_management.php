<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
 *
 * Gestion des rapports
 *  
 */
class Ca_reel_vs_potentiel_entite_management extends Rapports_management {

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
	 * On affiche les X premiers mois d'activité
	 * 
	 */	
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$entites = $this->entites($this->rapport);
		
		// on active l'export excel
		$this->export_excel($this->rapport);

        $titres = array('entite','ca_actuel_n', 'ca_previsionnel_n', 'ca_potentiel');

        foreach($titres as &$titre){
            $titre = traduction('rapport.ca_reel_vs_potentiel_entite.colonnes.'.$titre);
        }

		$this->rapport->titres($titres);
		
		if(empty($entites)) {

			$entites = modele('entite')->get()->pluck('id');
		}
		
		// on applique une saisonnalité automatiquement
		// attention, cette saisonnalité devrait être à définir par client
		// @todo
		$saisonnalite = array(
			1 => 7,
			2 => 13,
			3 => 20,
			4 => 28,
			5 => 35,
			6 => 46,
			7 => 52,
			8 => 54,
			9 => 66,
			10 => 78,
			11 => 90,
			12 => 100,
		);

        $mois_precedent = date('n',strtotime('now -1 month'));

		$avancement = $saisonnalite[$mois_precedent];
		
		// prorata pour le mois courant
		$prorata = date('d') / date('t');
		
		$avancement += (($saisonnalite[(int) date('m')] - $saisonnalite[$mois_precedent]) * $prorata);
		
		// on va chercher les résultats
		$calcul = new Calcul_gescom_management();
		
		$date_debut = date('Y-01-01');
		$date_fin = date('Y-m-d');
	
		$ca = $calcul->filtre_dates_quotidiennes(array('date_debut' => $date_debut, 'date_fin' => $date_fin))
						->filtre_entites($entites)
						->groupe_par_entite()
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
						
		foreach($entites as $entite_id) {
			
			$entite = modele('entite', $entite_id);
			
			// on calcule le prévisionnel (avancement M-1)
			if(!isset($ca[$entite_id]))
				$ca[$entite_id] = 0;
			
			$ca_previsionnel = $ca[$entite_id] * 100 / $avancement;
			
			$ca_potentiel = modele('client')->where('entite_id', $entite_id)->sum('potentiel');
			
			
			$this->rapport->ligne(array($entite->nom, montant_lisible($ca[$entite_id]), montant_lisible($ca_previsionnel), montant_lisible($ca_potentiel)));
		}
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}