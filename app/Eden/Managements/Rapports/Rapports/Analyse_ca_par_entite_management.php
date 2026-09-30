<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Analyse_ca_par_entite_management extends Rapports_management {

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
	 * On calcule le CA sur une période définie et comparaison au N-1
	 * 
	 */	
    public function genere($ajax = false) {
        
		$dates = $this->dates_mensuelles($this->rapport);
		$periodicite = $this->periodicite($this->rapport, $dates);
		$entites = $this->entites($this->rapport);


		$calcul = new Calcul_gescom_management();
		
		$dates_avec_annee_precedente = $dates;
		$dates_avec_annee_precedente['date_debut'] = date('Y-m-d', strtotime($dates['date_debut'].' -1 year'));
		
		$calcul->documents_valides_uniquement(false)
			//->debug()
			->filtre_dates_mensuelles($dates_avec_annee_precedente)
			->groupe_par('entite');
			
		if(in_array($periodicite['periodicite'], array('mensuelle', 'trimestrielle', 'semestrielle', 'annuelle'))) {
			
			$calcul->groupe_par('date_mensuelle');
		}
		else {
			
			$calcul->groupe_par('date');
		}
					
		$ca = $calcul->plus('facture_vente')->moins('avoir_vente')->resultat();

		// on fait les titres
		$titres = array('Entités');
		$sous_titres = array('');
		
		foreach($periodicite['periodes'] as $periode) {
			
			$titres[] = array($periode['nom'], '', 3);
			
			$sous_titres[] = 'N';
			$sous_titres[] = 'N-1';
			$sous_titres[] = '%';
		}

		
		$titres[] = array('Total', '', 3);


		$sous_titres[] = 'N';
		$sous_titres[] = 'N-1';
		$sous_titres[] = '%';



		$this->rapport->titres($titres);
		
		$this->rapport->sous_titre($sous_titres);

		$ligne_total = [];
		
		// on boucle pour chaque entité sur les résultats
		foreach($entites as $entite_id) {
			
			$entite = modele('entite', $entite_id);
			
			$ligne = array($entite->nom);
			$total_n = 0;
			$total_n_moins_1= 0;

			if(isset($ca[$entite->id])) {
					
				foreach($ca[$entite->id] as $date => $montant) {
					
					if(!isset($ca['total'])) 
						$ca['total'] = array();
					

					if(!isset($ca['total'][$date])) 
						$ca['total'][$date] = 0;
					

					$ca['total'][$date] += $montant;

				}

			}

			// on regarde pour chaque entité
			foreach($periodicite['periodes'] as $periode) {
				
				// aucune donnée
				if(!isset($ca[$entite->id])) {
					
					$ligne[] = '';
					$ligne[] = '';
					$ligne[] = '';
					
					continue;
				}

				// on boucle sur le tableau pour attribuer aux bonnes périodes
				$montant_total = 0;
				$montant_total_annee_precedente = 0;
				
				//dump($ca[$entite->id]);
				foreach($ca[$entite->id] as $date => $montant) {
					
					if($date >= $periode['date_debut'] && $date <= $periode['date_fin']) 
						$montant_total += $montant;


					if($date >= date('Y-m-d', strtotime($periode['date_debut_n_moins_1'])) && $date <= date('Y-m-d', strtotime($periode['date_fin_n_moins_1']))) 
						$montant_total_annee_precedente += $montant;

				}

				$total_n += round($montant_total);
				$total_n_moins_1 += round($montant_total_annee_precedente);

				if(empty($montant_total))
					$ligne[] = '';
				else
					$ligne[] = str_replace(' ', '&nbsp;', montant($montant_total,0));
				
				if(empty($montant_total_annee_precedente))
					$ligne[] = '';
				else
					$ligne[] = str_replace(' ', '&nbsp;', montant($montant_total_annee_precedente,0));
				
				// on calcule l'écart
				if(!empty($montant_total_annee_precedente)) {
					
					$ecart = round(($montant_total - $montant_total_annee_precedente) / $montant_total_annee_precedente * 100, 2).'%';
					$ligne[] = $ecart;	
				}
				elseif(!empty($montant_total)) {
					
					$ligne[] = 'n/a';	
				}
				else {
					
					$ligne[] = '';	
				}
			}

			$ligne[] = montant($total_n, 0);
			$ligne[] = montant($total_n_moins_1, 0);

			if(!empty($total_n_moins_1)) {

				$ligne[] = round(($total_n - $total_n_moins_1) / $total_n_moins_1 * 100, 2).'%';
			}
			elseif(!empty($total_n)) {

				$ligne[] = 'n/a';
			}
			else {

				$ligne[] = '';
			}
			
			$ligne_total[] = $ligne;
			$this->rapport->ligne($ligne);
		}


		// ligne total en bas du tableau

		$ligne = array('Total');
		$total_n = 0;
		$total_n_moins_1= 0;

		// on regarde pour chaque entité
		foreach($periodicite['periodes'] as $periode) {
				
			// aucune donnée
			if(!isset($ca['total'])) {
				
				$ligne[] = '';
				$ligne[] = '';
				$ligne[] = '';
				
				continue;
			}

			// on boucle sur le tableau pour attribuer aux bonnes périodes
			$montant_total = 0;
			$montant_total_annee_precedente = 0;
			
			foreach($ca['total'] as $date => $montant) {
				
				if($date >= $periode['date_debut'] && $date <= $periode['date_fin'])
					$montant_total += $montant;
				
				
				if($date >= date('Y-m-d', strtotime($periode['date_debut_n_moins_1'])) && $date <= date('Y-m-d', strtotime($periode['date_fin_n_moins_1']))) 
					$montant_total_annee_precedente += $montant;
			}

			$total_n += round($montant_total);
			$total_n_moins_1 += round($montant_total_annee_precedente);

			if(empty($montant_total))
				$ligne[] = '';
			else
				$ligne[] = str_replace(' ', '&nbsp;', montant($montant_total,0));
			
			if(empty($montant_total_annee_precedente))
				$ligne[] = '';
			else
				$ligne[] = str_replace(' ', '&nbsp;', montant($montant_total_annee_precedente,0));
			
			// on calcule l'écart
			if(!empty($montant_total_annee_precedente)) {
				
				$ecart = round(($montant_total - $montant_total_annee_precedente) / $montant_total_annee_precedente * 100, 2).'%';
				$ligne[] = $ecart;	
			}
			elseif(!empty($montant_total)) {
				
				$ligne[] = 'n/a';	
			}
			else {
				
				$ligne[] = '';	
			}
		}

		$ligne[] = montant($total_n, 0);
		$ligne[] = montant($total_n_moins_1, 0);

		if(!empty($total_n_moins_1)) {

			$ligne[] = round(($total_n - $total_n_moins_1) / $total_n_moins_1 * 100, 2).'%';
		}
		elseif(!empty($total_n)) {

			$ligne[] = 'n/a';
		}
		else {

			$ligne[] = '';
		}

		$this->rapport->ligne($ligne);

		return $this->rapport->genere($ajax);
    }
	
	
}