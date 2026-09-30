<?php

namespace App\Eden\Managements\Rapports\Rapports;

use App\Eden\Managements\Calcul_gescom_management;

use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Cr_parametrable_management extends Rapports_management {

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
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
		
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		$titres = array('Rubrique');
		
		foreach($dates['dates'] as $date) {
			
			$titres[] = $date['nom'];
		}
		
		$titres[] = 'Total';
		
		$this->rapport->titres($titres);
		
		// on va chercher les rubriques
		$rubriques = modele('rubrique_rapport_parametrable')->orderBy('ordre')->get();
		
		foreach($rubriques as $rubrique) {
			
			$total = array();
			
			foreach($dates['dates'] as $date) {
				
				$total[$date['periode']] = 0;
			}
			
			// on va chercher les lignes
			$lignes = modele('ligne_rapport_parametrable')->where('rubrique_rapport_parametrable_id', $rubrique->id)->orderBy('ordre')->get();
			
			foreach($lignes as $ligne_rapport) {
				
				$ligne = array($ligne_rapport->nom);
				
				$calcul = new Calcul_gescom_management;
				
				if(empty($ligne_rapport->contenu)) {
					
					$ca_par_famille = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->filtre_familles($ligne_rapport->familles()->toArray())
						->groupe_par_mois()
						->plus('facture_vente')
						->moins('avoir_vente')
						->resultat();
				}
				elseif($ligne_rapport->contenu == 1) {
					
					$ca_par_famille = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->filtre_familles($ligne_rapport->familles()->toArray())
						->groupe_par_mois()
						->plus('avoir_achat')
						->moins('facture_achat')
						->resultat();
						
				}
				else {
					
					$ca_par_famille = $calcul->documents_valides_uniquement(false)
						->filtre_entites($entites)
						->filtre_dates_mensuelles($dates)
						->filtre_familles($ligne_rapport->familles()->toArray())
						->groupe_par_mois()
						->plus('facture_vente')
						->moins('avoir_vente')
						->plus('avoir_achat')
						->moins('facture_achat')
						->resultat();
				}
				
				$total_ligne = 0;
							
				foreach($dates['dates'] as $date) {
					
					if(isset($ca_par_famille[$date['periode']])) {
						
						$ligne[] = montant($ca_par_famille[$date['periode']], 0);
						$total[$date['periode']] += $ca_par_famille[$date['periode']];
						$total_ligne += $ca_par_famille[$date['periode']];
					}
					else
						$ligne[] = montant(0, 0);
					
				}
				
				$ligne[] = montant($total_ligne, 2);
				
				$this->rapport->ligne($ligne);
				
			}
			
			
			$ligne = array('<b>'.$rubrique->nom.'</b>');
			$total_ligne = 0;
			
			foreach($total as $valeur) {
				
				$ligne[] = montant($valeur,0);
				$total_ligne += $valeur;
			}
			
			$ligne[] = montant($total_ligne, 2);
			
			$this->rapport->ligne($ligne);
		}
		
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
	
}