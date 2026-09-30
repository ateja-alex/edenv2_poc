<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Objectif_ca_par_famille_management extends Rapports_management {

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
		if (moi()->profil_id == 0) {

			$this->rapport->titre .= '
			    <a class="css__lien" href="'.route('base_eden.rapport.index', ['objectif_ca_par_famille_saisie']).'" style="margin-left: 50px;">
			        ' . traduction('rapport.objectif_ca_par_famille.titre_affichage') . '
                </a>';
		}

		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$entites = $this->entites($this->rapport);
		
		// on va chercher le CA par entité par mois
		$calcul = new Calcul_gescom_management();
		
		// $ca_par_famille = $calcul->documents_valides_uniquement(true)
		$ca_par_famille = $calcul->documents_valides_uniquement(false)
							->filtre_dates_mensuelles($dates)
							->whereIn('document.entite_id', $entites)
							->groupe_par('famille')
							->groupe_par('date_mensuelle')
							->plus('facture_vente')
							->moins('avoir_vente')
							->resultat();
							
		$familles = modele('famille')->orderBy('nom')->get();
		
		$titres = array(ucfirst(table_libre('famille')->element));
		
		foreach($dates['dates'] as $periode) {
			
			$titres[] = array($periode['nom'], 'css_montant');
		}
		
		$titres[] = array(traduction('rapport.objectif_ca_par_famille.colonnes.total'), 'css_montant');
		
		$this->rapport->titres($titres);
		
		$total_par_date = array();
		
		foreach($familles as $famille) {
			
			$total = 0;
			$total_objectif = 0;
			
			// ligne 1 : CA
			$ligne = array(management('famille', $famille->id)->affiche().' - '.traduction('rapport.objectif_ca_par_famille.ca'));
			
			foreach($dates['dates'] as $periode) {
				
				if(!isset($total_par_date[$periode['periode']]))
					$total_par_date[$periode['periode']] = array('objectif' => 0, 'realise' => 0);
				
				if(isset($ca_par_famille[$famille->id]) && isset($ca_par_famille[$famille->id][$periode['periode']])) {
					
					
					if(date('Y-m') <= $periode['periode'])
						$ligne[] = array(montant($ca_par_famille[$famille->id][$periode['periode']],0), 'css_montant css_case_grisee');
					else
						$ligne[] = array(montant($ca_par_famille[$famille->id][$periode['periode']],0), 'css_montant');
					
					$total += $ca_par_famille[$famille->id][$periode['periode']];
					
					$total_par_date[$periode['periode']]['realise'] += $ca_par_famille[$famille->id][$periode['periode']];
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
			$ligne = array(management('famille', $famille->id)->affiche().' - '.traduction('rapport.objectif_ca_par_famille.taux'));
			
			foreach($dates['dates'] as $periode) {
				
				
				// on va chercher l'objectif
				$objectif = modele('objectif_vente_par_famille')
								->whereIn('entite_id', $entites)
								->where('famille_id', $famille->id)
								->where('date', 'like', $periode['periode'].'%')
								->sum('objectif');
				
				
				$taux = false;
				if(!empty($objectif)) {
					
					$total_objectif += $objectif;
					
					if(isset($ca_par_famille[$famille->id]) && isset($ca_par_famille[$famille->id][$periode['periode']])) {
						
						$taux = round($ca_par_famille[$famille->id][$periode['periode']] / $objectif * 100);
					}
					else {
						
						$taux = 0;
					}
					
					$total_par_date[$periode['periode']]['objectif'] += $objectif;
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
					
					$taux .= ' % ('.$objectif.')';
					
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
						$ligne[] = array('-', 'css_montant css_case_grisee');
					else
						$ligne[] = array('-', 'css_montant');
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
		$ligne = array(traduction('rapport.objectif_ca_par_famille.total_periode')." - ".traduction('rapport.objectif_ca_par_famille.ca'));
		
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
		$ligne = array(traduction('rapport.objectif_ca_par_famille.total_periode')." - ".traduction('rapport.objectif_ca_par_famille.taux'));
		
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