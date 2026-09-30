<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Objectif_ca_par_entite_management extends Rapports_management {

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
	 * On compare les objectifs avec le réalisé pour chaque entité
	 * 
	 */
    public function genere($ajax = false) {
		
		// on crée une gestion de droit sur la possibilité de saisir les objectifs.
        $this->rapport->titre .= '
            <a class="css__lien" href="'.route('base_eden.rapport.index', ['objectif_ca_par_entite_saisie']).'" style="margin-left: 50px;">
                ' . traduction('rapport.objectif_ca_par_entite.titre_affichage') . '
            </a>';

		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		$this->export_excel($this->rapport);
		
		// on va chercher le CA par entité par mois
		$calcul = new Calcul_gescom_management();
		
		$ca_par_entite = $calcul->documents_valides_uniquement(true)
							->filtre_dates_mensuelles($dates)
							->groupe_par('entite')
							->groupe_par('date_mensuelle')
							->plus('facture_vente')
							->moins('avoir_vente')
							->resultat();

		$titres = array(ucfirst(table_libre('entite')->element));
		
		foreach($dates['dates'] as $periode) {
			
			$titres[] = array($periode['nom'], 'css_montant');
		}
		
		$titres[] = traduction('rapport.objectif_ca_par_entite.colonnes.total');
		
		$this->rapport->titres($titres);
		
		$total_par_date = array();
		
		foreach($entites as $entite_id) {
			
			// ligne 1 : CA
			$ligne = array(management('entite', $entite_id)->affiche().' - '.traduction('rapport.objectif_ca_par_entite.ca'));
			
			$total = 0;
			$total_objectif = 0;
			
			foreach($dates['dates'] as $periode) {
				
				
				
				if(!isset($total_par_date[$periode['periode']]))
					$total_par_date[$periode['periode']] = array('objectif' => 0, 'realise' => 0);
				
				if(isset($ca_par_entite[$entite_id]) && isset($ca_par_entite[$entite_id][$periode['periode']])) {
					
					if(date('Y-m') <= $periode['periode'])
						$ligne[] = array(montant($ca_par_entite[$entite_id][$periode['periode']],0), 'css_montant css_case_grisee');
					else
						$ligne[] = array(montant($ca_par_entite[$entite_id][$periode['periode']],0), 'css_montant');
					
					$total += $ca_par_entite[$entite_id][$periode['periode']];
					
					$total_par_date[$periode['periode']]['realise'] += $ca_par_entite[$entite_id][$periode['periode']];
				}
				else {
					
					if(date('Y-m') <= $periode['periode'])
						$ligne[] = array(montant(0,0), 'css_montant css_case_grisee');
					else
						$ligne[] = array(montant(0,0), 'css_montant');
					
				}
			}
			
			$ligne[] = array(montant($total,0), 'css_montant');
			
			$this->rapport->ligne($ligne);
			
			// ligne 2 : le % 
			$ligne = array(management('entite', $entite_id)->affiche().' - '.traduction('rapport.objectif_ca_par_entite.taux'));
			
			foreach($dates['dates'] as $periode) {
				
				// on va chercher l'objectif
				$objectif = modele('objectif_vente_par_entite')->where('entite_id', $entite_id)->where('date', 'like', $periode['periode'].'%')->first();
				
				
				$taux = false;
				if($objectif !== null && !empty($objectif->objectif)) {
					
					if(isset($ca_par_entite[$entite_id]) && isset($ca_par_entite[$entite_id][$periode['periode']])) {
						
						$taux = round($ca_par_entite[$entite_id][$periode['periode']] / $objectif->objectif * 100);
					}
					else {
						
						$taux = 0;
					}
					
					$total_objectif += $objectif->objectif;
					
					$total_par_date[$periode['periode']]['objectif'] += $objectif->objectif;
				}
				
				
				if($taux !== false) {
					
					if($taux < 40) {
						
						$couleur = '#ff6767';
					}
					elseif($taux < 80) {
						
						$couleur = '#ff9f4a';
					}
					else {
						
						$couleur = '#b4e25d';
					}
					
					$taux .= ' % ('.$objectif->objectif.')';
					
					if(date('Y-m') <= $periode['periode'])
						$taux = "<div style='background: $couleur; opacity: 0.5; height: 100%; text-align: center; color: white;'>$taux</div>";
					else
						$taux = "<div style='background: $couleur; height: 100%; text-align: center; color: white;'>$taux</div>";
					
					if(date('Y-m') <= $periode['periode'])
						$ligne[] = array($taux, 'css_montant css_case_grisee');
					else
						$ligne[] = array($taux, 'css_montant');
				}
				else {
					
					if(date('Y-m') <= $periode['periode'])
						$ligne[] = array($taux, 'css_montant css_case_grisee');
					else
						$ligne[] = array($taux, 'css_montant');
				}
				
				
			}
			
			// le total
			if(!empty($total_objectif)) {
				
				$taux = round($total / $total_objectif * 100);
				
				if($taux < 40) {
					
					$couleur = '#ff6767';
				}
				elseif($taux < 80) {
					
					$couleur = '#ff9f4a';
				}
				else {
					
					$couleur = '#b4e25d';
				}
				
				$taux .= ' %';
				
				$taux = "<div style='background: $couleur; height: 100%; text-align: center; color: white;'>$taux</div>";
				
				$ligne[] = array($taux, 'css_montant');
			}
			else {
				
				$ligne[] = array('-', 'css_montant');
			}
			
			$this->rapport->ligne($ligne);
		}
		
		
		// la ligne du total (réalisé)
		$ligne = array(traduction('rapport.objectif_ca_par_entite.total_periode_ca'));
		
		$total = 0;
		
		foreach($dates['dates'] as $periode) {
			
			if(!isset($total_par_date[$periode['periode']]))
				$total_par_date[$periode['periode']] = array('objectif' => 0, 'realise' => 0);
			
			if(isset($total_par_date[$periode['periode']]['realise'])) {
				
				$ligne[] = array(montant($total_par_date[$periode['periode']]['realise'],0), 'css_montant');
				
				$total += $total_par_date[$periode['periode']]['realise'];
			}
			else {
				
				$ligne[] = array(montant(0,0), 'css_montant');
			}
		}
		
		$ligne[] = array(montant($total,0), 'css_montant');
		
		$this->rapport->ligne_total($ligne);
		
		// la ligne du total (objectif)
		$ligne = array(traduction('rapport.objectif_ca_par_entite.total_periode_taux'));
		
		$total_objectif = 0;
		
		foreach($dates['dates'] as $periode) {
			
			
			// on va chercher l'objectif
			$objectif = $total_par_date[$periode['periode']]['objectif'];
			
			
			$taux = false;
			if(!empty($objectif)) {
				
				$total_objectif += $objectif;
				
				$taux = round($total_par_date[$periode['periode']]['realise'] / $objectif * 100);
			}
			
			
			if($taux !== false) {
				
				if($taux < 40) {
					
					$couleur = '#ff6767';
				}
				elseif($taux < 80) {
					
					$couleur = '#ff9f4a';
				}
				else {
					
					$couleur = '#b4e25d';
				}
				
				$taux .= ' %';
				
				if(date('Y-m') <= $periode['periode'])
					$taux = "<div style='background: $couleur; opacity: 0.5; height: 100%; text-align: center; color: white;'>$taux</div>";
				else
					$taux = "<div style='background: $couleur; height: 100%; text-align: center; color: white;'>$taux</div>";
				
				$ligne[] = array($taux, 'css_montant');
			}
			else {
				
				$ligne[] = array('-', 'css_montant');
			}
			
			
		}
		
		$taux = false;
		if(!empty($total_objectif)) {
			
			$taux = round($total / $total_objectif * 100);
		}
		
		
		if($taux !== false) {
			
			if($taux < 40) {
				
				$couleur = '#ff6767';
			}
			elseif($taux < 80) {
				
				$couleur = '#ff9f4a';
			}
			else {
				
				$couleur = '#b4e25d';
			}
			
			$taux .= ' %';
			
			$taux = "<div style='background: $couleur; height: 100%; text-align: center; color: white;'>$taux</div>";
			
			$ligne[] = array($taux, 'css_montant');
		}
		else {
			
			$ligne[] = array('-', 'css_montant');
		}
		
		$this->rapport->ligne_total($ligne);
		
		
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}