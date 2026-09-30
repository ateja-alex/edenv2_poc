<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;
use DB;

/**
 * 
 * Compte de résultat : on prend toutes les factures d'achat et de vente et on fait un total par famille
 * 
 */
class Compte_resultat_gescom_management extends Rapports_management {

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
	 * Compte de résultat : on prend toutes les factures d'achat et de vente et on fait un total par famille
	 * 
	 */
    public function genere($ajax = false) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		$this->export_excel($this->rapport);

		$titres = [ucfirst(table_libre('famille')->element)];
		
		foreach($dates['dates'] as $periode) {
			
			$titres[] = array($periode['nom'], 'css_montant');
		}
		
		$titres[] = array(traduction('rapport.compte_resultat_gescom.total'), 'css_montant');
		
		$this->rapport->titres($titres);
		
		// on va chercher les montants mensuels
		$ca_par_famille = $this->recupere_ca_par_famille($this->rapport);
		
		$info_familles = include(storage_path('app/eden_familles.php'));
		
		// on calcule les CA en cumulé
		$ca_par_famille_cumule = array();
		
		
		$familles_parents = $info_familles['familles_parents'];
		
		// on initialise avec les valeurs actuelles
		foreach($ca_par_famille as $id_famille => $ca_par_date) {
			
			foreach($ca_par_date as $date => $ca) {
				
				initialise_tableau($ca_par_famille_cumule, 0, $id_famille, $date);
				
				$ca_par_famille_cumule[$id_famille][$date] += $ca;
			}
		}

		// on cumule pour les familles parent
		foreach($ca_par_famille as $id_famille => $ca_par_date) {
			
			foreach($ca_par_date as $date => $ca) {
				
				if ( isset($familles_parents[$id_famille]) ) {

					foreach($familles_parents[$id_famille] as $id_famille_parent) {
						
						initialise_tableau($ca_par_famille_cumule, 0, $id_famille_parent, $date);
						
						$ca_par_famille_cumule[$id_famille_parent][$date] += $ca;
						
					}
				}
			}
		}
		
		$arborescence = $info_familles['arborescence'];

		$this->total_par_colonne = array();
		
		foreach($arborescence as $id_famille => $sous_familles) {
			
			$this->genere_ligne_compte_resultat($this->rapport, $dates, $ca_par_famille_cumule, $id_famille, $sous_familles);
		}
		
		// la ligne du total
		$ligne = array();
		$total = 0;
		
		// le nom de la famille
		$ligne[] = 'Total période';
		
		// pour chaque date, le montant total
		foreach($dates['dates'] as $id_date => $periode) {
			
			// la ligne du total de la famille
			if(isset($this->total_par_colonne[$id_date])) {
				
				$ligne[] = array(montant($this->total_par_colonne[$id_date], 0), 'css_montant');
				$total += $this->total_par_colonne[$id_date];
			}
			else
				$ligne[] = array('', 'css_montant');
		}
		
		$ligne[] = array(montant($total, 0), 'css_montant');
		
		$this->rapport->ligne($ligne);
		
		return $this->rapport->genere($ajax);
    }
	
	/**
	 * 
	 * Récupère le CA par famille (pour simplifier une éventuelle surcharge chez un client)
	 * 
	 */
	protected function recupere_ca_par_famille($rapport) {
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		$calcul = new Calcul_gescom_management();
		
		// récupère le CA par famille
		$ca_par_famille = $calcul->documents_valides_uniquement(true)
							->filtre_entites($entites)
							->filtre_dates_mensuelles($dates)
							->groupe_par('famille')
							->groupe_par('date_mensuelle')
							->plus('facture_vente')
							->moins('avoir_vente')
							->moins('facture_achat')
							->plus('avoir_achat')
							->resultat();
							
		return $ca_par_famille;
	}
	
	/**
	 * 
	 * Génère une ligne pour le tableau
	 * 
	 */
	protected function genere_ligne_compte_resultat($rapport, $dates, $ca_par_famille_cumule, $id_famille, $sous_familles, $niveau = 0) {
		
		$ligne = array();
		$total = 0;
		
		// le nom de la famille
		$ligne[] = '<div style="padding-left: '.($niveau * 50).'px;">'.management('famille', $id_famille)->affiche().'</div>';
		
		// pour chaque date, le montant total
		foreach($dates['dates'] as $id_date => $periode) {
			
			// la ligne du total de la famille
			if(isset($ca_par_famille_cumule[$id_famille]) && isset($ca_par_famille_cumule[$id_famille][$periode['periode']])) {
				
				$ligne[] = array(montant($ca_par_famille_cumule[$id_famille][$periode['periode']], 0), 'css_montant');
				$total += $ca_par_famille_cumule[$id_famille][$periode['periode']];
				
				if(!isset($this->total_par_colonne[$id_date]))
					$this->total_par_colonne[$id_date] = 0;
				
				$this->total_par_colonne[$id_date] += $ca_par_famille_cumule[$id_famille][$periode['periode']];
			}
			else
				$ligne[] = array('', 'css_montant');
		}
		
		// les sous familles
		foreach($sous_familles as $id_sous_famille => $sous_sous_familles) {
			
			$niveau++;
			$this->genere_ligne_compte_resultat($this->rapport, $dates, $ca_par_famille_cumule, $id_sous_famille, $sous_sous_familles, $niveau);
			$niveau--;
		}
		
		$ligne[] = array(montant($total, 0), 'css_montant');
		
		$this->rapport->ligne($ligne);
	}
	
	
}